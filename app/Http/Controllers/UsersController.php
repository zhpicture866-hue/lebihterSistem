<?php
   
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Religion;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
 
class UsersController extends Controller
{
    
    public function index(Request $request) 
    {
    
        if ($request->ajax()) {

            $auth = auth()->user();

            if ($auth->hasRole('Customer')) {
                 $users = User::query();
            }  else if ($auth->hasAnyRole(['Pemilik Lisensi', 'Karyawan'])) {
                $users = User::where('license_id', $auth->license_id)
                 ->whereHas('roles', function ($q) {
                     $q->whereIn('name', ['Pemilik Lisensi', 'Karyawan']);
                 });
}
 else {
            // Default: hanya user itu sendiri
            $users = User::all();
        }

            return Datatables::of($users)

            ->addIndexColumn()

            ->addColumn('province_name', fn($row) => $row->province->name ?? '-')
            ->addColumn('city_name', fn($row) => $row->city->name ?? '-')
            ->addColumn('district_name', fn($row) => $row->district->name ?? '-')
            ->addColumn('sub_district_name', fn($row) => $row->subDistrict->name ?? '-')
            ->addColumn('postal_code', fn($row) => $row->postalCode->postal_code ?? '-')
            ->addColumn('religion_name', fn($row) => $row->religion->name ?? '-')
            ->addColumn('birth_date', fn($row) => $row->birth_date ? Carbon::parse($row->birth_date)->format('d/m/Y') : '-')
            ->addColumn('gender', fn($row) => $this->readableGender($row->gender))
            ->addColumn('identity_number', fn($row) => $row->identity_number ?? '-')
            ->addColumn('npwp', fn($row) => $row->npwp ?? '-')
            ->addColumn('phone', fn($row) => $row->phone ?? '-')
            ->editColumn('fullname', function ($row) {
                    $url = route('users.show', $row->id);
                    $name = Str::title($row->fullname ?? '-');
                    return '<a href="'.$url.'">'.e($name).'</a>';
                })

            // ->addColumn('action', function($user) {
            //     $editUrl = route('users.edit', $user->id);

            //     return ' 
            //         <a href="'.$editUrl.'" class="btn btn-success btn-sm">Edit</a>
            //         <button data-id="'.$user->id.'" class="btn btn-danger btn-sm delete-user">Delete</button>
            //     '; 
            // })

            ->addColumn('action', function ($user) {
                    $buttons = '';
                    {
                        $buttons .= '<a href="' . route('users.edit', $user->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    {
                        $buttons .= '<a href="' . route('users.show', $user->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';
                    }
                    {
                        $buttons .= '<button data-id="' . $user->id . '" class="btn btn-icon btn-sm btn-dark delete-user" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })

            ->rawColumns(['action', 'fullname'])
            ->make(true);
        }
          
        return view('users.index');
    }

    private function readableGender($value)
    {
        return match ((int) $value) {
            1 => 'Laki - Laki',
            2 => 'Perempuan',
            default => '-',
        };
    }

     public function create() 
    {
        $user = auth()->user();
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        return view('users.create', compact('religions', 'provinces', 'roles', 'user'));
    }

        public function store(Request $request)
{
    $validated = $request->validate([
        'fullname' => 'required',
        'nickname' => 'nullable',
        'gender' => 'nullable|in:1,2',
        'birth_place' => 'nullable',
        'birth_date' => 'nullable|date_format:Y-m-d',
        'email' => [
            'required',
            'email',
            Rule::unique(User::class, 'email'),
        ],
        'password' => 'required|min:8',
        // 'role' => 'required|array',
        // 'role.*' => 'string|exists:roles,name',
        'religion_id' => [
            'nullable',
            Rule::exists(Religion::class, 'id'),
        ],
        'province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique(User::class, 'identity_number'),
        ],
        'npwp' => 'nullable|string|max:30',
        'address' => 'nullable',
        'city_id' => ['nullable', Rule::exists(City::class, 'id')],
        'district_id' => ['nullable', Rule::exists(District::class, 'id')],
        'sub_district_id' => ['nullable', Rule::exists(SubDistrict::class, 'id')],
        'postal_code_id' => ['nullable', Rule::exists(PostalCode::class, 'id')],
        'phone' => 'nullable',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    DB::beginTransaction();

    try {
        // Upload photo jika ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')
                ->storeAs(
                    'photos',
                    Str::uuid() . '.' . $request->file('photo')->getClientOriginalExtension(),
                    'public'
                );
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Simpan user
        $user = User::create($validated);

        // Assign role
        // $user->syncRoles($validated['role']);

        DB::commit();

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil ditambahkan.');

    } catch (\Exception $e) {
        DB::rollBack();

        // Jika foto sudah terupload tapi gagal simpan ke database, hapus filenya
        if (isset($validated['photo'])) {
            \Storage::disk('public')->delete($validated['photo']);
        }

        return back()
            ->withErrors(['error' => 'Gagal menambahkan pengguna: ' . $e->getMessage()])
            ->withInput();
    }
}

public function show(User $user)
{
    return view('users.show', compact('user'));
}


    public function edit(User $user) 
    {
        $religions = Religion::all();
        $provinces = Province::all();
        $cities = City::where('province_id', $user->province_id)->get();
        $districts = District::where('city_id', $user->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $user->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $user->sub_district_id)->get();
        $roles = Role::all();
        $selectedRoles = $user->roles->pluck('name')->toArray();
       return view('users.edit', compact('user', 'religions', 'roles', 'selectedRoles', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes'));
    }

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'fullname' => 'required',
        'nickname' => 'nullable',
        'gender' => 'nullable|in:1,2',
        'birth_place' => 'nullable',
        'birth_date' => 'nullable|date_format:Y-m-d',

        'email' => [
            'required',
            'email',
            Rule::unique(User::class, 'email')
                ->ignore($user->id, 'id'),
        ],

        'password' => 'nullable|min:8',

        // 'role' => 'required|array',
        // 'role.*' => 'string|exists:roles,name',

        'religion_id' => [
            'nullable',
            Rule::exists(Religion::class, 'id'),
        ],

        'province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],

        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique(User::class, 'identity_number')
                ->ignore($user->id, 'id'),
        ],

        'npwp' => 'nullable|string|max:30',
        'address' => 'nullable',

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

        'phone' => 'nullable|regex:/^[0-9]+$/',

        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    DB::beginTransaction();

    $newPhotoPath = null;
    $oldPhotoPath = $user->photo;

    try {
        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->storeAs(
                'photos',
                Str::uuid() . '.' .
                $request->file('photo')->getClientOriginalExtension(),
                'public'
            );

            $validated['photo'] = $newPhotoPath;
        }
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);

        DB::commit();

        if (
            $newPhotoPath &&
            $oldPhotoPath &&
            $oldPhotoPath !== $newPhotoPath &&
            Storage::disk('public')->exists($oldPhotoPath)
        ) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'Data pengguna berhasil diperbarui.');

    } catch (\Throwable $e) {
        DB::rollBack();

        if (
            $newPhotoPath &&
            Storage::disk('public')->exists($newPhotoPath)
        ) {
            Storage::disk('public')->delete($newPhotoPath);
        }

        return back()
            ->withErrors([
                'error' => 'Gagal memperbarui pengguna: ' . $e->getMessage()
            ])
            ->withInput();
    }
}

    public function destroy(User $user) 
    {
    
        if ($user) {
            $user->delete();
            return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }

}