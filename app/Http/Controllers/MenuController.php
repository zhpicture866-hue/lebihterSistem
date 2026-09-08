<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Menu::with('parent')->orderBy('order');

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('parent_name', function ($row) {
                return $row->parent?->text ?? '-';
            })

            ->addColumn('active_badge', function ($row) {
                return $row->is_active
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })

                ->addColumn('actions', function ($menu) {
                    $buttons = '';
                    if (auth()->user()->can('ubah data menu')) {
                        $buttons .= '<a href="' . route('menus.edit', $menu->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Ubah">
                                        <i class="ti ti-edit"></i>
                                    </a>';
                    }
                    if (auth()->user()->can('lihat data menu')) {
                        $buttons .= '<a href="' . route('menus.show', $menu->id) . '" class="btn btn-icon btn-sm btn-dark me-1" title="Lihat">
                                        <i class="ti ti-eye"></i>
                                    </a>';

                    }
                    if (auth()->user()->can('hapus data menu')) {
                        $buttons .= '<button data-id="' . $menu->id . '" class="btn btn-icon btn-sm btn-dark delete-menu" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>';
                    }
                    return $buttons;
                })

            ->rawColumns(['active_badge','actions'])
            ->make(true);
    }

    return view('menus.index');
}

    public function create()
    {
        $parents = Menu::whereNull('parent_id')->get();
        $permissions = Permission::all();

        return view('menus.create', compact('parents', 'permissions'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'text' => 'required|string|max:255',
        'url' => 'nullable|string|max:255',
        'type' => 'in:route,url,label',
        'key' => 'nullable|string|max:255',
        'parent_id' => [
            'nullable',
            Rule::exists(Menu::class, 'id'),
        ],
        'order' => 'integer',
        'icon' => 'nullable|string|max:255',
        'is_active' => 'boolean',

        // ✅ UNTUK MULTIPLE
        'permission_name'   => 'nullable|array',
        'permission_name.*' => 'string|max:255',
    ]);

    // ✅ SIMPAN SEBAGAI "a|b|c"
    if ($request->filled('permission_name')) {
        $data['permission_name'] = implode('|', $request->permission_name);
    } else {
        $data['permission_name'] = null;
    }

    Menu::create($data);

    return redirect()
        ->route('menus.index')
        ->with('success', 'Menu berhasil dibuat.');
}


public function edit(Menu $menu)
{
    $parents = Menu::where('id', '!=', $menu->id)
        ->orderBy('text')
        ->get();

    $permissions = Permission::orderBy('name')->get();

    return view('menus.edit', compact('menu', 'parents', 'permissions'));
}

public function update(Request $request, $id)
{
    $validated = $request->validate([
        'text' => 'required|string|max:255',
        'url' => 'nullable|string|max:255',
        'type' => 'required|string|max:50',

        'parent_id' => [
            'nullable',
            Rule::exists(Menu::class, 'id'),
        ],

        'order' => 'integer',
        'icon' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'permission_name' => 'nullable|array',
        'permission_name.*' => 'string|max:255',
    ]);

    // Gabungkan permission menjadi string "a|b|c"
    if ($request->filled('permission_name')) {
        $validated['permission_name'] = implode('|', $request->permission_name);
    } else {
        $validated['permission_name'] = null;
    }

    $menu = Menu::findOrFail($id);
    $menu->update($validated);

    return redirect()
        ->route('menus.index')
        ->with('success', 'Menu berhasil diubah.');
}
    public function destroy(Menu $menu) 
    {
    
        if ($menu) {
            $menu->delete();
            return response()->json(['status' => 'success', 'message' => 'User deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }
}
