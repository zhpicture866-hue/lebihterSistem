<?php

namespace App\Http\Controllers;

use App\Models\ProjectType;
use App\Models\ProjectTypeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ProjectTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ProjectType::withCount('projects');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('is_active', fn ($row) => $row->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>')
                ->addColumn('projects_count', fn ($row) => $row->projects_count . ' project')
                ->addColumn('action', function ($row) {
                    $editUrl = route('project_types.edit', $row->id);

                    $buttons = '<a href="' . $editUrl . '" class="btn btn-icon btn-sm btn-primary text-white">
                                    <i class="ti ti-edit"></i></a>';

                    // Jangan izinkan hapus kalau masih ada project yang pakai tipe ini
                    if ($row->projects_count === 0) {
                        $buttons .= ' <button data-id="' . $row->id . '"
                                        class="btn btn-icon btn-sm btn-dark delete-project-type">
                                        <i class="ti ti-trash"></i></button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['is_active', 'action'])
                ->make(true);
        }

        return view('project_types.index');
    }

    /**
     * ================== CREATE ==================
     */
    public function create()
    {
        // Dipakai form buat kasih 1 baris kosong default di UI repeater step
        $projectType = new ProjectType();
        $levels      = collect([['level_order' => 1, 'level_name' => '']]);

        return view('project_types.create', compact('projectType', 'levels'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        DB::transaction(function () use ($validated) {
            $projectType = ProjectType::create([
                'name'        => $validated['name'],
                'code'        => $validated['code'],
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            $this->syncLevels($projectType, $validated['levels']);
        });

        return redirect()
            ->route('project_types.index')
            ->with('success', 'Jenis proyek berhasil ditambahkan.');
    }

    /**
     * ================== EDIT ==================
     */
    public function edit(ProjectType $projectType)
    {
        $levels = $projectType->levelTemplates()->get(['level_order', 'level_name']);

        if ($levels->isEmpty()) {
            $levels = collect([['level_order' => 1, 'level_name' => '']]);
        }

        return view('project_types.edit', compact('projectType', 'levels'));
    }

    public function update(Request $request, ProjectType $projectType)
    {
        $validated = $this->validateRequest($request, $projectType->id);

        DB::transaction(function () use ($validated, $projectType) {
            $projectType->update([
                'name'        => $validated['name'],
                'code'        => $validated['code'],
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            $this->syncLevels($projectType, $validated['levels']);
        });

        return redirect()
            ->route('project_types.index')
            ->with('success', 'Jenis proyek berhasil diperbarui.');
    }

    public function destroy(ProjectType $projectType)
    {
        if ($projectType->projects()->exists()) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Jenis proyek ini masih dipakai oleh project yang sudah ada, tidak bisa dihapus.',
            ], 422);
        }

        DB::transaction(function () use ($projectType) {
            $projectType->levelTemplates()->delete();
            $projectType->delete();
        });

        return response()->json(['status' => 'success', 'message' => 'Jenis proyek berhasil dihapus.']);
    }

    /**
     * ================== HELPERS ==================
     */
    private function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => [
                'required', 'string', 'max:100', 'alpha_dash',
                function ($attribute, $value, $fail) use ($ignoreId) {
                    // Dibuat manual (bukan Rule::unique) karena Rule::unique/exists
                    // memecah string di tanda titik sebagai NAMA KONEKSI, bukan skema —
                    // jadi 'zhpicture.project_types' akan dibaca sebagai koneksi
                    // "zhpicture" yang tidak ada di config/database.php.
                    $exists = DB::table('zhpicture.project_types')
                        ->where('code', $value)
                        ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                        ->exists();

                    if ($exists) {
                        $fail('Kode jenis proyek ini sudah dipakai, pakai kode lain.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',

            // repeater step di form: levels[0][level_name], levels[1][level_name], dst
            'levels'                  => 'required|array|min:1',
            'levels.*.level_name'     => 'required|string|max:255',
        ]);
    }

    /**
     * Ganti seluruh template step lama dengan yang baru dari form.
     * Dilakukan replace-all (bukan diff) karena ini cuma template, bukan data project berjalan.
     */
    private function syncLevels(ProjectType $projectType, array $levels): void
    {
        $projectType->levelTemplates()->delete();

        $rows = collect($levels)
            ->values()
            ->map(fn ($level, $index) => [
                'project_type_id' => $projectType->id,
                'level_order'     => $index + 1, // urutan diambil dari urutan input di form, bukan input manual
                'level_name'      => $level['level_name'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ])
            ->toArray();

        ProjectTypeLevel::insert($rows);
    }
}
