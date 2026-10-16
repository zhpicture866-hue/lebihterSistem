<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    private const GUARD = 'sistem_b';
public function index(Request $request)
{
    if ($request->ajax()) {

        $query = Permission::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($permission) {
                return optional($permission->created_at)->translatedFormat('d F Y H:i');
            })
            ->addColumn('actions', function ($permission) {

                $buttons = '';

                if (auth()->user()->can('ubah permission')) {
                    $buttons .= '<a href="' . route('permissions.edit', $permission->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Ubah">
                        <i class="ti ti-edit"></i>
                    </a>';
                }

                if (auth()->user()->can('hapus permission')) {
                    $buttons .= '<button
                        " class="btn btn-icon btn-sm btn-dark delete-permissions" title="Hapus"
                        data-id="'.$permission->id.'">
                        <i class="ti ti-trash"></i>
                    </button>';
                }

                return $buttons;
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    return view('permissions.index');
}

public function create()
{
    return view('permissions.create');
}

public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->where('guard_name', self::GUARD),
            ],
            'modules' => 'nullable',
        ]);

        Permission::create([
            'id'         => Str::uuid(),
            'name'       => $request->name,
            'guard_name' => self::GUARD,
            'modules'    => $request->modules,
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission berhasil ditambahkan.');
    }


public function edit(Permission $permission)
{
    return view('permissions.edit', compact('permission'));
}

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', self::GUARD)
                    ->ignore($permission->id),
            ],
            'modules' => 'nullable',
        ]);

        $permission->update([
            'name'    => $request->name,
            'modules' => $request->modules,
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', 'Permission berhasil diubah.');
    }
    public function destroy(Permission $permission) 
    {
    
        if ($permission) {
            $permission->delete();
            return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }
}
