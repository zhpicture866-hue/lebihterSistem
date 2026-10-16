<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Menu;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')
            ->orderByRaw("CASE WHEN name = 'Super-Admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        $permissions = Permission::all();

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function updatePermissions(Request $request, $id)
{
    $role = Role::findOrFail($id);

    // 🧩 Filter hanya UUID valid (hindari angka 0, null, dll)
    $permissionIds = collect($request->input('permissions', []))
        ->filter(fn($id) => is_string($id) && preg_match('/^[0-9a-fA-F-]{36}$/', $id))
        ->values()
        ->toArray();

    if (empty($permissionIds)) {
        $role->syncPermissions([]); // Hapus semua jika kosong
    } else {
        // 🔹 Ambil nama permission berdasarkan UUID valid
        $permissionNames = Permission::whereIn('id', $permissionIds)
            ->pluck('name')
            ->toArray();

        $role->syncPermissions($permissionNames);
    }

    return response()->json([
        'success' => true,
        'message' => 'Permissions berhasil diperbarui.'
    ]);
}

    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        return view('roles.create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
            'role_group' => 'required|in:Internal,Eksternal',
            'external_model' => 'nullable|string'
        ]);
        // if ($request->role_group === 'Eksternal' && !$request->external_model) {
        //     return back()
        //         ->withErrors(['external_model' => 'External model wajib dipilih untuk role Eksternal'])
        //         ->withInput();
        // }

        $role = Role::create([
            'name' => $request->name,
            'role_group' => $request->role_group,
            'external_model' => $request->external_model,
            'guard_name' => 'web'
        ]);
        $role->syncPermissions($request->permissions ?? []);
        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $groupedPermissions = $this->getGroupedPermissions();

        return view('roles.edit', compact(
            'role',
            'groupedPermissions'
        ));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->ignore($role->id),
            ],
            'role_group' => 'required|in:Internal,Eksternal',
            'external_model' => 'nullable|string'
        ]);

        // if ($request->role_group === 'Eksternal' && !$request->external_model) {
        //     return back()
        //         ->withErrors(['external_model' => 'External model wajib dipilih untuk role Eksternal'])
        //         ->withInput();
        // }

        if ($request->role_group === 'Internal') {
            $request->merge([
                'external_model' => null
            ]);
        }

        $role->update([
            'name' => $request->name,
            'role_group' => $request->role_group,
            'external_model' => $request->external_model
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role diperbarui.');
    }

    public function destroy(Role $role)
    {
        try {

            \DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->delete();

            $role->delete();

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role berhasil dihapus.');

        } catch (\Exception $e) {

            return redirect()
                ->route('roles.index')
                ->with('error', 'Terjadi kesalahan saat menghapus.');

        }
    }
        private function getGroupedPermissions()
{
    $permissions = Permission::all();

    $permissionGroups = $permissions->groupBy('modules');

    $parents = Menu::with([
        'children' => function ($q) {
            $q->orderBy('order');
        }
    ])
    ->whereNull('parent_id')
    ->orderBy('order')
    ->get();

    // Mapping permission -> module
    $permissionMap = Permission::pluck('modules', 'name');

    $groupedPermissions = collect();

    foreach ($parents as $parent) {

        foreach ($parent->children as $child) {

            if (!$child->permission_name) {
                continue;
            }

            $module = $permissionMap[$child->permission_name] ?? null;

            if (!$module) {
                continue;
            }

            if (!$groupedPermissions->has($module)) {
                $groupedPermissions[$module] = $permissionGroups[$module] ?? collect();
            }
        }
    }

    // Tambahkan module yang belum ada di menu
    foreach ($permissionGroups as $module => $items) {
        if (!$groupedPermissions->has($module)) {
            $groupedPermissions[$module] = $items;
        }
    }

    return $groupedPermissions;
}
}
