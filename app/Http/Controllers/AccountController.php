<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $auth = auth()->user();

            if ($auth->hasRole('Super-Admin')) {
                 $users = User::query();
            }  else if ($auth->hasAnyRole(['Pemilik Lisensi', 'Karyawan'])) {
                $users = User::where('license_id', $auth->license_id)
                 ->whereHas('roles', function ($q) {
                     $q->whereIn('name', ['Pemilik Lisensi', 'Karyawan']);
                 });
}
 else {
            // Default: hanya user itu sendiri
            $users = User::with('roles');
        }

            $roles = Role::pluck('name')->toArray();

        return DataTables::of($users)
            ->addColumn('role_dropdown', function ($user) use ($roles) {
                $currentRoles = $user->getRoleNames()->toArray();
                $dropdown = '<select class="form-select role-dropdown select2" multiple data-user-id="'.$user->id.'">';
                foreach ($roles as $role) {
                    $selected = in_array($role, $currentRoles) ? 'selected' : '';
                    $dropdown .= "<option value='{$role}' {$selected}>{$role}</option>";
                }
                $dropdown .= '</select>';
                return $dropdown;
            })
            ->rawColumns(['role_dropdown'])
            ->make(true);
        }
          
        return view('accounts.index');
    }

    // public function updateRole(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|uuid|exists:users,id',
    //         'roles' => 'array',
    //         'roles.*' => 'string|exists:roles,name',
    //     ]);

    //     $user = User::findOrFail($request->user_id);

    //     $employeeRoles = config('employee_roles.roles');
    //     $externalRoles = config('eksternal_roles.roles');
    //     $roleModelMap  = config('eksternal_roles.models');

    //     DB::transaction(function () use ($user, $request, $employeeRoles, $externalRoles, $roleModelMap) {
    //         // 🔹 Sinkronisasi role ke user (pakai Spatie)
    //         $user->syncRoles($request->roles ?? []);

    //         $isEmployee = $user->roles()->whereIn('name', $employeeRoles)->exists();

    //         if ($isEmployee) {
    //             $roleName = $user->roles()->whereIn('name', $employeeRoles)->first()?->name ?? 'Staff';

    //             Employee::firstOrCreate(
    //                 ['user_id' => $user->id],
    //                 [
    //                    'nik' => Employee::generateNik(),
    //                     'marital_status' => 1,
    //                     'position' => [$roleName],
    //                     'employment_status' => 'Aktif',
    //                     'start_date' => now()->format('Y-m-d'),
    //                     'basic_salary' => 0,
    //                     'allowance' => 0,
    //                     'deduction' => 0,
    //                     'bonus' => 0,
    //                     'thr' => 0,
    //                     'contract_letter_file' => 'dokumen.pdf',
    //                 ]
    //             );
    //         } else {
    //             $user->employee()?->delete();
    //         }

    //         $rolesUser = $user->roles->pluck('name')->toArray();

    //         foreach ($externalRoles as $roleName) {
    //             $modelClass = $roleModelMap[$roleName] ?? null;
    //             if (!$modelClass || !class_exists($modelClass)) continue;

    //             if (in_array($roleName, $rolesUser)) {
    //                 // Jika user punya role ini → buat record
    //                 $modelClass::firstOrCreate(
    //                     ['user_id' => $user->id],
    //                     $modelClass::getDefaultAttributes($user)
    //                 );
    //             } else {
    //                 // Jika role dicabut → hapus record
    //                 $modelClass::where('user_id', $user->id)->delete();
    //             }
    //         }
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Role berhasil diperbarui dan data eksternal disinkronkan.',
    //     ]);
    // }
    public function updateRole(Request $request)
{
    $request->validate([
        'user_id' => ['required','uuid', Rule::exists(User::class,'id')],
        'roles' => 'array',
        'roles.*' => 'string|exists:roles,name',
    ]);

    $user = User::findOrFail($request->user_id);

    DB::transaction(function () use ($user, $request) {

        $user->syncRoles($request->roles ?? []);

        $user->load('roles');

        $internalRoles = $user->roles
            ->where('role_group', 'Internal');

        if ($internalRoles->isNotEmpty()) {

            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => Employee::generateNik(),

                    'position' => $internalRoles
                        ->pluck('name')
                        ->values()
                        ->toArray(),

                    'marital_status' => 1,
                    'employment_status' => 'Aktif',
                    'start_date' => now()->format('Y-m-d'),
                    'basic_salary' => 0,
                    'allowance' => 0,
                    'deduction' => 0,
                    'bonus' => 0,
                    'thr' => 0,
                    'contract_letter_file' => 'dokumen.pdf',
                ]
            );

        } else {

            if ($user->employee) {

                if (!$user->employee->projects()->exists()) {

                    $user->employee->delete();

                }

            }

        }

        foreach ($user->roles->where('role_group', 'Eksternal') as $role) {

            if (!$role->external_model) {
                continue;
            }

            if (!class_exists($role->external_model)) {
                continue;
            }

            $modelClass = $role->external_model;

            $modelClass::firstOrCreate(
                ['user_id' => $user->id],
                $modelClass::getDefaultAttributes($user)
            );
        }

        $externalModels = \App\Models\Role::whereNotNull('external_model')
            ->pluck('external_model');

        foreach ($externalModels as $modelClass) {

            if (!class_exists($modelClass)) {
                continue;
            }

            $stillHasRole = $user->roles
                ->contains(fn ($role) =>
                    $role->external_model === $modelClass
                );

            if (!$stillHasRole) {
                $modelClass::where('user_id', $user->id)->delete();
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Role berhasil diperbarui dan tersinkron otomatis.',
    ]);
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
