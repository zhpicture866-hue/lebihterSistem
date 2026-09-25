<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Religion;
use App\Models\Employee;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Role;
use App\Models\Bank;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    
    public function index(Request $request)
    {
        $auth = auth()->user();

        $query = Employee::with(['user.roles']);

        if ($auth->can('lihat data karyawan') && !$auth->can('lihat daftar karyawan')) {
            $query->where('user_id', $auth->id);
        }

        if ($request->ajax()) {
        return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('fullname', function ($row) {
                    return $row->user->fullname ?? '-';
                })
                ->addColumn('email', function ($row) {
                    return $row->user->email ?? '-';
                })
                ->addColumn('roles', function ($row) {
                    return $row->user?->roles?->pluck('name')->implode(', ') ?: '-';
                })

                ->editColumn('fullname', function ($row) {
                    $url = route('employees.show', $row->id);
                    $name = Str::title($row->user->fullname ?? '-');
                    return '<a href="'.$url.'">'.e($name).'</a>';
                })
                ->editColumn('contract_letter_file', function ($row) {
                    if ($row->contract_letter_file) {
                        // Ambil URL file lewat Storage::url
                        $url = Storage::url($row->contract_letter_file);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->editColumn('training_certificate', function ($row) {
                    if ($row->training_certificate) {
                        // Ambil URL file lewat Storage::url
                        $url = Storage::url($row->training_certificate);

                        return '<a href="' . $url . '" target="_blank">
                                    <i class="ti ti-file-text"></i> Lihat Dokumen
                                </a>';
                    }

                    return '<span class="text-muted">Belum ada</span>';
                })
                ->addColumn('action', function ($employee) {
                    $buttons = '';
                    if (auth()->user()->can('ubah data karyawan')) {
                        $buttons .= '<a href="' . route('employees.edit', $employee->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('lihat data karyawan')) {
                        $buttons .= '<a href="' . route('employees.show', $employee->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';

                    }
                    if (auth()->user()->can('hapus data karyawan')) {
                        $buttons .= '<button data-id="' . $employee->id . '" class="btn btn-icon btn-sm btn-dark delete-employee" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['fullname', 'action', 'contract_letter_file', 'training_certificate'])
                ->make(true);
        }

        return view('employees.index');
    }

    public function create()
    {
        $user = auth()->user();
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::internal()
            ->orderBy('name')
            ->get();
        return view('employees.create', compact('user', 'roles', 'religions', 'provinces'));
    }

    public function store(Request $request)
{

    $validated = $request->validate([
        'user_id' => ['nullable','uuid', Rule::exists(User::class,'id')],
        'fullname' => 'required|string|max:255',
        'nickname' => 'nullable|string|max:100',
        'gender' => 'nullable|in:1,2',
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
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:255',
        'province_id' => [
            'nullable',
            Rule::exists(Province::class, 'id'),
        ],
        'city_id' => ['nullable', Rule::exists(City::class, 'id')],
        'district_id' => ['nullable', Rule::exists(District::class, 'id')],
        'sub_district_id' => ['nullable', Rule::exists(SubDistrict::class, 'id')],
        'postal_code_id' => ['nullable', Rule::exists(PostalCode::class, 'id')],
        'bank_id' => ['nullable','uuid', Rule::exists(Bank::class,'id')],
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|max:50',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'identity_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',

        // --- data employee ---
        'nik' => 'required|unique:employees,nik',
        'role' => 'required|array',
        'role.*' => 'string|exists:roles,name',
        'marital_status' => 'nullable|in:1,2,3,4',
        'employment_status' => 'nullable|in:Tetap,Kontrak,Harian,Honorer',
        'start_date' => ['nullable', 'date_format:Y-m-d'],
        'basic_salary' => ['nullable', 'numeric', 'min:0'],
        'allowance' => ['nullable', 'numeric', 'min:0'],
        'deduction' => ['nullable', 'numeric', 'min:0'],
        'bonus' => ['nullable', 'numeric', 'min:0'],
        'thr' => ['nullable', 'numeric', 'min:0'],

        // --- file ---
        'contract_letter_file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        'training_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
    ]);

    // 🔹 Upload foto karyawan (kalau ada)
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

if ($request->hasFile('identity_photo')) {

    $identityPhotoPath = $request->file('identity_photo')->storeAs(
        'identity_photos',
        Str::uuid().'.'.$request->file('identity_photo')->getClientOriginalExtension(),
        'public'
    );

    $validated['identity_photo'] = $identityPhotoPath;
}

if ($request->hasFile('contract_letter_file')) {
    $filename = Str::uuid().'.'.$request->file('contract_letter_file')->getClientOriginalExtension();

    $path = $request->file('contract_letter_file')->storeAs(
        'contracts',
        $filename,
        'public'
    );

    // simpan full relative path
    $validated['contract_letter_file'] = $path;   // → photos/uuid.jpg
}

if ($request->hasFile('training_certificate')) {
    $filename = Str::uuid().'.'.$request->file('training_certificate')->getClientOriginalExtension();

    $path = $request->file('training_certificate')->storeAs(
        'certificates',
        $filename,
        'public'
    );

    // simpan full relative path
    $validated['training_certificate'] = $path;   // → photos/uuid.jpg
}

    DB::transaction(function () use ($validated, $request) {

        // 🔹 Cek apakah ada user_id (kalau tidak, buat user baru)
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);
        } else {
            // Generate username sederhana dan password random
            $username = Str::slug($validated['fullname']);
            $password = '12345678'; // bisa diganti jadi 'default123' kalau mau

            // Buat user baru
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
                'province_id' => $validated['province_id'],
                'city_id' => $validated['city_id'],
                'district_id' => $validated['district_id'],
                'sub_district_id' => $validated['sub_district_id'],
                'postal_code_id' => $validated['postal_code_id'],
                'email' => $validated['email'],
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'photo' => $validated['photo'] ?? null,
                'bank_id' => $validated['bank_id'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'account_holder' => $validated['account_holder'] ?? null,
                'identity_photo' => $validated['identity_photo'] ?? null,
            ]);

            // Simpan password plain-nya di session supaya admin bisa lihat
            session()->flash('new_user_password', $password);
        }

        // 🔹 Assign role
        $user->assignRole($validated['role']);

        // 🔹 Simpan employee
        Employee::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'nik' =>  $validated['nik'],
            'marital_status' => $validated['marital_status'] ?? null,
            'employment_status' => $validated['employment_status'] ?? null,
            'start_date' => $validated['start_date'],
            'basic_salary' => $validated['basic_salary'],
            'allowance' => $validated['allowance'],
            'deduction' => $validated['deduction'],
            'bonus' => $validated['bonus'],
            'thr' => $validated['thr'],
            'contract_letter_file' => $validated['contract_letter_file'] ?? null,
            'training_certificate' => $validated['training_certificate'] ?? null,
        ]);
    });

    return redirect()
        ->route('employees.index')
        ->with('success', 'Data karyawan berhasil ditambahkan.' . 
            (session('new_user_password') ? ' Akun user baru dibuat. Password: ' . session('new_user_password') : '')
        );
}

public static function generateNikAjax()
{
    $lastNumber = Employee::where('nik', 'like', 'E-%')
        ->selectRaw("
            MAX(
                CAST(
                    REGEXP_REPLACE(nik, '[^0-9]', '', 'g')
                    AS INTEGER
                )
            ) as max_nik
        ")
        ->value('max_nik');

    $newNumber = ($lastNumber ?? 0) + 1;
    $nik = 'E-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

    return response()->json([
        'nik' => $nik
    ]);
}

public function show(Employee $employee)
{
    $employee->load([
        'user.roles',
    ]);

    // $attendances = Attendance::where('employee_id', $employee->id)
    //     ->latest('attendance_date')
    //     ->paginate(10);

    return view('sdm.employees.show', compact(
        'employee'
    ));
}

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $user = $employee->user()->with('bank')->first();
        $religions = Religion::all();
        $provinces = Province::all();
        $roles = Role::all();
        $internalRoles = Role::where('role_group', 'Internal')
            ->orderBy('name')
            ->pluck('name');
        $selectedRoles = $user->roles->pluck('name')->toArray();
        $cities = City::where('province_id', $user->province_id)->get();
        $districts = District::where('city_id', $user->city_id)->get();
        $subDistricts = SubDistrict::where('district_id', $user->district_id)->get();
        $postalCodes = PostalCode::where('sub_district_id', $user->sub_district_id)->get();
        

        return view('employees.edit', compact('user', 'roles', 'internalRoles', 'employee',
        'religions', 'provinces', 'cities', 'districts', 'subDistricts', 'postalCodes', 'selectedRoles'));
    }

    public function update(Request $request, Employee $employee)
{
    $validated = $request->validate([
        // --- data user ---
        'fullname' => 'required|string|max:255',
        'birth_place' => 'nullable|string|max:255',
        'birth_date' => 'nullable|date',
        'gender' => 'nullable|in:1,2',
        'phone' => 'nullable|string|max:20',
        
        'email' => [
            'required',
            'email',
            Rule::unique(User::class, 'email')
                ->ignore($employee->user_id),
        ],

        'address' => 'nullable|string',
        'identity_number' => [
            'nullable',
            'regex:/^[0-9]{16}$/',
            Rule::unique(User::class, 'identity_number')
                ->ignore($employee->user_id),
        ],
        'religion_id' => [
            'nullable',
            Rule::exists(Religion::class, 'id'),
        ],
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
        'bank_id' => ['nullable','uuid', Rule::exists(Bank::class,'id')],
        'account_number' => 'nullable|string|max:50',
        'account_holder' => 'nullable|string|max:100',
        'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        'identity_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        'nik' => 'required|string|max:50|unique:employees,nik,' . $employee->id,
        'role' => 'required|array',
        'role.*' => 'string|exists:roles,name',
        'marital_status' => 'nullable|in:1,2,3,4',
        'employment_status' => 'nullable|in:Tetap,Kontrak,Harian,Honorer',
        'start_date' => ['nullable', 'date_format:Y-m-d'],
        'basic_salary' => ['nullable', 'numeric', 'min:0'],
        'allowance' => ['nullable', 'numeric', 'min:0'],
        'deduction' => ['nullable', 'numeric', 'min:0'],
        'bonus' => ['nullable', 'numeric', 'min:0'],
        'thr' => ['nullable', 'numeric', 'min:0'],

        // --- file ---
        'contract_letter_file' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
        'training_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:2048'],
    ]);

    DB::transaction(function () use ($request, $validated, $employee) {

    $user = $employee->user;

    // 🔹 Update data user
    $user->update([
        'fullname' => $validated['fullname'],
        'birth_place' => $validated['birth_place'] ?? null,
        'birth_date' => $validated['birth_date'] ?? null,
        'email' => $validated['email'],
        'identity_number' => $validated['identity_number'],
        'gender' => $validated['gender'] ?? null,
        'phone' => $validated['phone'] ?? null,
        'address' => $validated['address'] ?? null,
        'religion_id' => $validated['religion_id'],
        'province_id' => $validated['province_id'],
        'city_id' => $validated['city_id'],
        'district_id' => $validated['district_id'],
        'sub_district_id' => $validated['sub_district_id'],
        'postal_code_id' => $validated['postal_code_id'],
        'bank_id' => $validated['bank_id'] ?? null,
        'account_number' => $validated['account_number'] ?? null,
        'account_holder' => $validated['account_holder'] ?? null,
    ]);

    // 🔹 Update role user (hapus role lama → assign role baru)
    $user->syncRoles($validated['role']);
    
    if ($request->hasFile('identity_photo')) {
        if ($user->identity_photo) {
            Storage::disk('public')->delete($user->identity_photo);
        }

        $path = $request->file('identity_photo')->storeAs(
            'identity_photos',
            Str::uuid().'.'.$request->file('identity_photo')->getClientOriginalExtension(),
            'public'
        );

        $user->update(['identity_photo' => $path]);
    }
    if ($request->hasFile('photo')) {
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $path = $request->file('photo')->storeAs(
            'photos',
            Str::uuid().'.'.$request->file('photo')->getClientOriginalExtension(),
            'public'
        );

        $user->update(['photo' => $path]);
    }

    if ($request->hasFile('contract_letter_file')) {
        if ($employee->contract_letter_file) {
            Storage::disk('public')->delete($employee->contract_letter_file);
        }

        $validated['contract_letter_file'] = $request->file('contract_letter_file')->storeAs(
            'contracts',
            Str::uuid().'.'.$request->file('contract_letter_file')->getClientOriginalExtension(),
            'public'
        );
    }

    if ($request->hasFile('training_certificate')) {
        if ($employee->training_certificate) {
            Storage::disk('public')->delete($employee->training_certificate);
        }

        $validated['training_certificate'] = $request->file('training_certificate')->storeAs(
            'certificates',
            Str::uuid().'.'.$request->file('training_certificate')->getClientOriginalExtension(),
            'public'
        );
    }

    $employee->update([
        'nik' => $validated['nik'],
        'marital_status' => $validated['marital_status'],
        'employment_status' => $validated['employment_status'],
        'start_date' => $validated['start_date'],
        'basic_salary' => $validated['basic_salary'],
        'allowance' => $validated['allowance'],
        'deduction' => $validated['deduction'],
        'bonus' => $validated['bonus'],
        'thr' => $validated['thr'],
        'contract_letter_file' => $validated['contract_letter_file'] ?? $employee->contract_letter_file,
        'training_certificate' => $validated['training_certificate'] ?? $employee->training_certificate,
    ]);
    });

    return redirect()
        ->route('employees.show', $employee->id)
        ->with('success', 'Data karyawan berhasil diperbarui.');
}

    public function destroy(Employee $employee)
    {
        if ($employee) {
            $employee->delete();
            return response()->json(['status' => 'success', 'message' => 'Employee deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }
}
