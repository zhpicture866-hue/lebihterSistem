<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Religion;
use App\Models\Customer;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Bank;
use App\Helpers\ActiveRole;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class CustomersController extends Controller
{
            private function authorizeCustomer(Customer $customer, string $permission): void
    {
        abort_unless(ActiveRole::hasPermission($permission), 403);

        if (!ActiveRole::hasPermission('lihat daftar customer')
            && $customer->user_id !== auth()->id()) {
            abort(403);
        }
    }

public function index(Request $request)
{


    $auth = auth()->user();
 
    // Query dasar (sudah dibatasi hak akses). Dipakai untuk tabel DAN kartu statistik.
    $baseQuery = Customer::with(['user.roles']);
 
    if (!ActiveRole::hasPermission('lihat daftar customer')) {
        $baseQuery->where('user_id', $auth->id);
    }

    if ($request->ajax()) {
        // withCount hanya di query tabel (bukan di $baseQuery) supaya perhitungan kartu tidak terganggu
        $query = (clone $baseQuery)->withCount('projects');
 
        // Filter tab (Lead, Prospek, New Customer, Silver, Gold, Platinum)
        if ($request->filled('loyalty')) {
            $query->where('loyalty_level', $request->loyalty);
        }
 
        $customers = $query->get();
 
        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('customer', function ($row) {
                $name     = Str::title($row->user->fullname ?? '-');
                $email    = $row->user->email ?? '-';
                $initials = collect(explode(' ', trim($name)))
                    ->filter()->take(2)
                    ->map(fn($w) => mb_substr($w, 0, 1))
                    ->implode('');
                $url = route('customers.show', $row->id);
 
                return '<div class="d-flex align-items-center">
                            <span class="cust-avatar av-' . (crc32($name) % 5) . '">' . e(strtoupper($initials)) . '</span>
                            <div class="ms-3">
                                <a href="' . $url . '" class="cust-name">' . e($name) . '</a>
                                <div class="cust-email">' . e($email) . '</div>
                            </div>
                        </div>';
            })
            ->editColumn('channel', fn($row) => $row->readable_channel)
            ->addColumn('category', fn($row) => $this->readableCategory($row->category))
            ->addColumn('orders', fn($row) => (int) $row->projects_count)
            ->addColumn('last_contact', fn($row) => $row->created_at
                ? $row->created_at->locale('id')->diffForHumans()   // "3 hari yang lalu"
                : '-')
            ->addColumn('loyalty', function ($row) {
                // NOTE: pakai angka loyalty_level untuk warna (bukan teks),
                // karena sebelumnya match('1','2',...) tidak pernah cocok dengan teks "Lead", dst.
                $level = (int) $row->loyalty_level;
                return '<span class="lv-badge lv-' . $level . '">' . e($row->readable_loyalty_level) . '</span>';
            })
            ->addColumn('action', function ($customer) {
                $buttons = '<div class="d-flex justify-content-end gap-1">';
                if (ActiveRole::hasPermission('ubah data customer')) {
                    $buttons .= '<a href="' . route('customers.edit', $customer->id) . '" class="btn btn-icon btn-sm btn-ghost-dark" title="Ubah"><i class="ti ti-edit"></i></a>';
                }
                if (ActiveRole::hasPermission('lihat data customer')) {
                    $buttons .= '<a href="' . route('customers.show', $customer->id) . '" class="btn btn-icon btn-sm btn-ghost-dark" title="Lihat"><i class="ti ti-eye"></i></a>';
                }
                // if (auth()->user()->can('lihat data crm')) {
                //     $buttons .= '<a href="' . route('crm.customers.show', $customer->id) . '" class="btn btn-icon btn-sm btn-ghost-dark" title="CRM"><i class="ti ti-address-book"></i></a>';
                // }
                if (ActiveRole::hasPermission('hapus data customer')) {
                    $buttons .= '<button data-id="' . $customer->id . '" class="btn btn-icon btn-sm btn-ghost-danger delete-customer" title="Hapus"><i class="ti ti-trash"></i></button>';
                }
                return $buttons . '</div>';
            })
            ->rawColumns(['customer', 'loyalty', 'action'])
            ->with('stats', $this->loyaltyStats($baseQuery)) // supaya kartu ikut ter-update setelah hapus
            ->make(true);
    }
 
    return view('customers.index', [
        'stats' => $this->loyaltyStats($baseQuery),
    ]);
}
private function loyaltyStats($baseQuery): array
{
    $counts = (clone $baseQuery)
        ->toBase()
        ->reorder()
        ->selectRaw('loyalty_level, COUNT(*) as total')
        ->groupBy('loyalty_level')
        ->pluck('total', 'loyalty_level');
 
    $stats = [];
    foreach (range(1, 6) as $level) {
        $stats[$level] = (int) ($counts[$level] ?? 0);
    }
 
    return $stats;
}
    private function readableCategory($value)
    {
        return match ((int) $value) {
            1 => 'Individu',
            2 => 'Perusahaan',
            3 => 'Instansi',
            4 => 'Lainnya',
            default => '-',
        };
    }

    private function readableLoyaltyLevel($value)
    {
        return match ((int) $value) {
            1 => 'Lead',
            2 => 'Prospek',
            3 => 'New Customer',
            4 => 'Silver',
            5 => 'Gold',
            6 => 'Platinum',
            default => '-',
        };
    }

    public function create(Customer $customer)
    {
        $user = auth()->user();
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        return view('customers.create', compact('customer', 'user', 'roles', 'religions', 'provinces'));
    }

    public function store(Request $request)
{
    $code = preg_replace('/\D/', '', $request->phone_code ?? '');
    $num  = ltrim(preg_replace('/\D/', '', $request->phone_number ?? ''), '0');

    if ($code === '62' && str_starts_with($num, '62')) {
        $num = substr($num, 2);
    }

    $request->merge(['phone' => $num !== '' ? $code . $num : null]);
    $validated = $request->validate([
        'user_id' => ['nullable','uuid', Rule::exists(User::class,'id')],
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
        'title' => 'nullable|in:1,2,3',
        'email' => [
            'required',
            'email',
            Rule::unique(User::class, 'email'),
        ],
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique(User::class, 'identity_number'),
        ],
        'religion_id' => [
            'nullable',
            Rule::exists(Religion::class, 'id'),
        ],
        'npwp' => 'nullable|string|max:30',
        'phone_code' => ['nullable', 'required_with:phone_number', Rule::in(array_keys(config('phone_codes')))],
        'phone'      => ['nullable', 'regex:/^[1-9][0-9]{7,14}$/'],
        'address' => 'nullable|string|max:255',
        'user_province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'user_city_id' => ['nullable', Rule::exists(City::class, 'id')],
        'user_district_id' => ['nullable', Rule::exists(District::class, 'id')],
        'user_sub_district_id' => ['nullable', Rule::exists(SubDistrict::class, 'id')],
        'user_postal_code_id' => ['nullable', Rule::exists(PostalCode::class, 'id')],
        'bank_id' => ['nullable','uuid', Rule::exists(Bank::class,'id')],
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|string|max:50',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        // --- data Customer ---
        'nic' => 'required|unique:customers,nic',
        'shipping_name' => 'nullable|string|max:255',
        'shipping_phone' => 'nullable|string|max:20',
        'shipping_address' => 'nullable|string|max:255',
        'province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'city_id' => ['nullable', Rule::exists(City::class, 'id')],
        'district_id' => ['nullable', Rule::exists(District::class, 'id')],
        'sub_district_id' => ['nullable', Rule::exists(SubDistrict::class, 'id')],
        'postal_code_id' => ['nullable', Rule::exists(PostalCode::class, 'id')],
        'role' => 'nullable|array',
        'role.*' => 'string|exists:roles,name',
        'category' => 'nullable|in:1,2,3,4',
        'channel' => ['nullable', Rule::in(array_keys(Customer::CHANNELS))],
        'loyalty_level' => 'nullable|in:1,2,3,4,5,6'
    ]);

    if ($request->hasFile('photo')) {
        $filename = Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension();

        $path = $request->file('photo')->storeAs(
            'photos',
            $filename,
            'public'
        );

        // simpan full relative path
        $validated['photo'] = $path;   // → photos/uuid.jpg
    }

    // Jika alamat pengiriman sama dengan domisili user
        if ($request->has('same_address')) {
            $validated['province_id'] = $validated['user_province_id'];
            $validated['city_id'] = $validated['user_city_id'];
            $validated['district_id'] = $validated['user_district_id'];
            $validated['sub_district_id'] = $validated['user_sub_district_id'];
            $validated['postal_code_id'] = $validated['user_postal_code_id'];
            $validated['shipping_address'] = $validated['address'];
            $validated['shipping_name'] = $validated['fullname'];
            $validated['shipping_phone'] = $validated['phone'];
        }

    DB::transaction(function () use ($validated) {

        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
        } else {
            $password = '12345678';
            $user = User::create([
                'id' => Str::uuid(),
                'fullname' => $validated['fullname'],
                'nickname' => $validated['nickname'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'identity_number' => $validated['identity_number'],
                'npwp' => $validated['npwp'],
                'address' => $validated['address'],
                'religion_id' => $validated['religion_id'],
                'province_id' => $validated['user_province_id'],
                'city_id' => $validated['user_city_id'],
                'district_id' => $validated['user_district_id'],
                'sub_district_id' => $validated['user_sub_district_id'],
                'postal_code_id' => $validated['user_postal_code_id'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'phone' => $validated['phone'] ?? null,
                'phone_code' => $validated['phone_code'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'title' => $validated['title'] ?? null,
                'photo' => $validated['photo'] ?? null,
                'bank_id' => $validated['bank_id'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder' => $validated['account_holder'] ?? null,
            ]);

            session()->flash('new_user_password', $password);
        }

        if (!empty($validated['role'])) {
            foreach ($validated['role'] as $r) {
                if (!$user->hasRole($r)) {
                    $user->assignRole($r);
                }
            }
        }

        Customer::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'nic' => $validated['nic'],
            'shipping_name' => $validated['shipping_name'],
            'shipping_phone' => $validated['shipping_phone'],
            'shipping_address' => $validated['shipping_address'],
            'province_id' => $validated['province_id'],
            'city_id' => $validated['city_id'],
            'district_id' => $validated['district_id'],
            'sub_district_id' => $validated['sub_district_id'],
            'postal_code_id' => $validated['postal_code_id'],
            'category' => $validated['category'] ?? null,
            'channel' => $validated['channel'] ?? null,
            'loyalty_level' => !empty($validated['loyalty_level'])
                ? (int) $validated['loyalty_level']
                : 1,
            'photo' => $validated['photo'] ?? null,
        ]);
    });

    return redirect()
        ->route('customers.index')
        ->with('success', 'Data customer berhasil ditambahkan.' .
            (session('new_user_password') ? ' Akun user baru dibuat. Password: ' . session('new_user_password') : '')
        );
}

public static function generateNicAjax()
{
    $lastNumber = Customer::where('nic', 'like', 'C-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(nic, '[^0-9]', '', 'g')
                    AS INTEGER
                )
            ) as max_nic
        ")
        ->value('max_nic');

    $newNumber = ($lastNumber ?? 0) + 1;
    $nic = 'C-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

    return response()->json([
        'nic' => $nic
    ]);
}

 public function show(Customer $customer)
    {
        $this->authorizeCustomer($customer, 'lihat data customer');
        $customer->load('user');
        return view('sdm.customers.show', [
            'user' => $customer->user,
            'customer' => $customer
        ]);
    }

    public function edit($id)
    {
        $customer = Customer::with(['user.roles', 'user.bank'])->findOrFail($id);
        $this->authorizeCustomer($customer, 'ubah data customer');

        $user = $customer->user;
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        $selectedRoles = $user->roles->pluck('name')->toArray();
        $cities = City::where('province_id', $user->province_id)->get();
        $districts = District::where('city_id', $user->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $user->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $user->sub_district_id)->get();
        

        return view('customers.edit', compact('user', 'roles', 'selectedRoles', 'customer',
        'religions', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes'));
    }

public function update(Request $request, Customer $customer)
{
    $this->authorizeCustomer($customer, 'ubah data customer');

    // Hanya peran pengelola yang boleh mengubah peran, kategori, dan loyalty
    $canManage = ActiveRole::hasPermission('lihat daftar customer');
    $code = preg_replace('/\D/', '', $request->phone_code ?? '');
    $num  = ltrim(preg_replace('/\D/', '', $request->phone_number ?? ''), '0');

    if ($code === '62' && str_starts_with($num, '62')) {
        $num = substr($num, 2);
    }

    $request->merge(['phone' => $num !== '' ? $code . $num : null]);
    $validated = $request->validate([
        // --- data user ---
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
        'email' => [
            'required',
            'email',
            Rule::unique(User::class, 'email')
                ->ignore($customer->user_id),
        ],
        'birth_place' => 'nullable|string|max:100',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique(User::class, 'identity_number')
                ->ignore($customer->user_id),
        ],
        'religion_id' => [
            'nullable',
            Rule::exists(Religion::class, 'id'),
        ],
        'npwp' => 'nullable|string|max:30',
        'phone'      => ['nullable', 'regex:/^[1-9][0-9]{7,14}$/'],
        'address' => 'nullable|string|max:255',
        'user_province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'user_city_id' => ['nullable', Rule::exists(City::class, 'id')],
        'user_district_id' => ['nullable', Rule::exists(District::class, 'id')],
        'user_sub_district_id' => ['nullable', Rule::exists(SubDistrict::class, 'id')],
        'user_postal_code_id' => ['nullable', Rule::exists(PostalCode::class, 'id')],
        'bank_id' => ['nullable','uuid', Rule::exists(Bank::class,'id')],
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|string|max:50',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        // --- data customer ---
        'nic' => 'nullable|string|max:50|unique:customers,nic,' . $customer->id,
        'shipping_name' => 'nullable|string|max:255',
        'shipping_phone' => 'nullable|string|max:20',
        'shipping_address' => 'nullable|string|max:255',
        'province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'city_id' => [
            'nullable',
            Rule::exists(City::class, 'id'),
        ],
        'district_id' => [
            'nullable',
            Rule::exists(District::class, 'id'),
        ],

        'sub_district_id' => [
            'nullable',
            Rule::exists(SubDistrict::class, 'id'),
        ],

        'postal_code_id' => [
            'nullable',
            Rule::exists(PostalCode::class, 'id'),
        ],
        'category' => 'nullable|in:1,2,3,4',
        'loyalty_level' => 'nullable|in:1,2,3,4,5,6',
        'channel' => ['nullable', Rule::in(array_keys(Customer::CHANNELS))],
        'role' => 'nullable|array',
        'role.*' => ['string', Rule::exists('roles', 'name')->where('role_group', 'Eksternal')],
    ]);

    // Member biasa tidak boleh menyentuh peran, kategori, maupun loyalty
    if (!$canManage) {
        unset($validated['role'], $validated['category'], $validated['loyalty_level']);
    }

    // Alamat pengiriman disamakan dengan alamat user
    if ($request->has('same_address')) {
        $validated['province_id']      = $validated['user_province_id'] ?? null;
        $validated['city_id']          = $validated['user_city_id'] ?? null;
        $validated['district_id']      = $validated['user_district_id'] ?? null;
        $validated['sub_district_id']  = $validated['user_sub_district_id'] ?? null;
        $validated['postal_code_id']   = $validated['user_postal_code_id'] ?? null;
        $validated['shipping_address'] = $validated['address'] ?? null;
        $validated['shipping_name']    = $validated['fullname'];
        $validated['shipping_phone']   = $validated['phone'] ?? null;
    }

    $newPhotoPath = null;   // foto baru yang baru diunggah
    $oldPhotoPath = null;   // foto lama yang dihapus setelah transaksi berhasil

    try {
        DB::transaction(function () use ($validated, $customer, $request, $canManage, &$newPhotoPath, &$oldPhotoPath) {
            $user = $customer->user;

            if ($request->hasFile('photo')) {
                $newPhotoPath = $request->file('photo')->storeAs(
                    'photos',
                    Str::uuid() . '.' . $request->file('photo')->getClientOriginalExtension(),
                    'public'
                );

                $oldPhotoPath = $user->photo;
                $validated['photo'] = $newPhotoPath;
            }

            $user->update([
                'fullname' => $validated['fullname'],
                'nickname' => $validated['nickname'] ?? null,
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'identity_number' => $validated['identity_number'] ?? null,
                'religion_id' => $validated['religion_id'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'address' => $validated['address'] ?? null,
                'province_id' => $validated['user_province_id'] ?? null,
                'city_id' => $validated['user_city_id'] ?? null,
                'district_id' => $validated['user_district_id'] ?? null,
                'sub_district_id' => $validated['user_sub_district_id'] ?? null,
                'postal_code_id' => $validated['user_postal_code_id'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'bank_id' => $validated['bank_id'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder' => $validated['account_holder'] ?? null,
                'photo' => $validated['photo'] ?? $user->photo,
            ]);

            // Peran hanya boleh ditambah oleh pengelola
            if ($canManage && !empty($validated['role'])) {
                foreach ($validated['role'] as $roleName) {
                    if (!$user->hasRole($roleName)) {
                        $user->assignRole($roleName);
                    }
                }
            }

            $customer->update([
                'nic' => $validated['nic'] ?? null,
                'shipping_name' => $validated['shipping_name'] ?? null,
                'shipping_phone' => $validated['shipping_phone'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'district_id' => $validated['district_id'] ?? null,
                'sub_district_id' => $validated['sub_district_id'] ?? null,
                'postal_code_id' => $validated['postal_code_id'] ?? null,
                'channel' => $validated['channel'] ?? null,
                'category' => $canManage
                    ? ($validated['category'] ?? null)
                    : $customer->category,
                'loyalty_level' => $canManage
                    ? (!empty($validated['loyalty_level']) ? (int) $validated['loyalty_level'] : 1)
                    : $customer->loyalty_level,
                'photo' => $validated['photo'] ?? $customer->photo,
            ]);
        });
    } catch (\Throwable $e) {
        // Transaksi gagal: buang foto baru supaya tidak jadi file yatim
        if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
            Storage::disk('public')->delete($newPhotoPath);
        }

        throw $e;
    }

    // Transaksi berhasil: baru hapus foto lama
    if ($oldPhotoPath && Storage::disk('public')->exists($oldPhotoPath)) {
        Storage::disk('public')->delete($oldPhotoPath);
    }

    return redirect()
        ->route('customers.show', $customer->id)
        ->with('success', 'Data customer berhasil diperbarui.');
}

public function destroy(Customer $customer): JsonResponse
{
    abort_if($customer->user_id === auth()->id(), 403, 'Tidak bisa menghapus akun sendiri.');
    $this->authorizeCustomer($customer, 'hapus data customer');

    $photosToDelete = [];

    DB::transaction(function () use ($customer, &$photosToDelete) {
        $user = $customer->user;

        if ($customer->photo) {
            $photosToDelete[] = $customer->photo;
        }

        $customer->delete();

        if ($user) {
            $roles = $user->roles->pluck('name')->toArray();

            if (count($roles) === 1 && in_array('Customer', $roles)) {
                // Member adalah satu-satunya peran: hapus akun beserta fotonya
                if ($user->photo) {
                    $photosToDelete[] = $user->photo;
                }

                $user->delete();
            } else {
                // Masih punya peran lain: cabut peran Member saja
                $memberRoleId = Role::where('name', 'Customer')->value('id');

                $user->removeRole('Customer');

                // Kalau peran aktifnya Member, pindahkan ke peran lain yang tersisa
                if ($memberRoleId && $user->active_role === $memberRoleId) {
                    $user->update([
                        'active_role' => $user->roles()->first()?->id,
                    ]);
                }
            }
        }
    });

    // File dihapus setelah transaksi sukses, supaya data tidak hilang kalau rollback
    foreach (array_unique($photosToDelete) as $path) {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Data customer berhasil dihapus.',
    ]);
}

}