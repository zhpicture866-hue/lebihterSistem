<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SystemController extends Controller
{
    public function index(Request $request)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        if ($request->ajax()) {
            $query = System::withCount(['plans', 'subscriptions']);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('is_active', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>')
                ->addColumn('plans_count', fn ($row) => $row->plans_count)
                ->addColumn('subscriptions_count', fn ($row) => $row->subscriptions_count)
                ->addColumn('action', function ($row) {
                    $editUrl = route('systems.edit', $row->id);

                    $buttons = '<a href="' . $editUrl . '" class="btn btn-icon btn-sm btn-primary"><i class="ti ti-edit"></i></a>';
                    $buttons .= '<button data-id="' . $row->id . '" class="btn btn-icon btn-sm btn-dark delete-system"><i class="ti ti-trash"></i></button>';

                    return $buttons;
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('systems.index');
    }

    public function create()
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        return view('systems.create');
    }

    public function store(Request $request)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'base_url'  => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['slug']      = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $system = System::create($validated);

        return redirect()
            ->route('systems.index')
            ->with('success', 'Sistem berhasil ditambahkan. Sekarang tambahkan minimal satu plan untuk sistem ini.');
    }

    public function edit(System $system)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $system->load('plans');

        return view('systems.edit', compact('system'));
    }

    public function update(Request $request, System $system)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'base_url'  => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        // slug sengaja tidak ikut diubah otomatis kalau nama berubah,
        // supaya tidak merusak referensi slug yang sudah dipakai di sistem lain
        // (mis. base_url callback, koneksi database *_legacy). Ubah manual kalau memang perlu.
        $validated['is_active'] = $request->boolean('is_active', true);

        $system->update($validated);

        return back()->with('success', 'Data sistem berhasil diperbarui.');
    }

    public function destroy(System $system)
    {
        abort_if(auth()->user()->cannot('kelola sistem'), 403);

        if ($system->subscriptions()->exists()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Sistem ini masih punya subscription aktif/riwayat, tidak bisa dihapus. Nonaktifkan saja.',
            ], 422);
        }

        $system->delete();

        return response()->json(['status' => 'success', 'message' => 'Sistem berhasil dihapus.']);
    }

private function uniqueSlug(string $name, ?string $ignoreId = null): string
{
    $originalSlug = Str::slug($name);
    $slug = $originalSlug;
    $counter = 1;

    while (
        System::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
    ) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}
}
