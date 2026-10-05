<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Affiliator;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\RabProcess;
use App\Models\OfferProcessItem; 
use App\Models\User;
use App\Models\Province;
use App\Services\ProjectNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $auth = auth()->user();

        $query = Project::with([
            'customer.user:id,fullname',
            'employee.user:id,fullname',
            'affiliator.user:id,fullname',
            'projectType:id,name',
            'levels:id,project_id,level_order,level_name,is_completed',
        ]);

        // Batasi data kalau user cuma boleh lihat proyek sendiri
        if (
            $auth->can('lihat data proyek') &&
            !$auth->can('lihat daftar proyek')
        ) {
            $query->where(function ($q) use ($auth) {
                $q->whereHas('customer', fn ($qq) => $qq->where('user_id', $auth->id))
                  ->orWhereHas('employee', fn ($qq) => $qq->where('user_id', $auth->id));
            });
        }

        if ($request->ajax()) {

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('customer', fn ($row) => $row->customer?->user?->fullname ?? '-')
                ->addColumn('employee', fn ($row) => $row->employee?->user?->fullname ?? '-')
                ->addColumn('affiliator', fn ($row) => $row->affiliator?->user?->fullname ?? '-')
                ->addColumn('start_date', fn ($row) => $row->start_date
                    ? Carbon::parse($row->start_date)->format('d/m/Y')
                    : '-')
                ->addColumn('project_type', function ($row) {

                    $name = $row->projectType?->name ?? '-';

                    return '<span class="badge bg-info">'
                        . e($name) .
                        '</span>';
                })
                ->addColumn('current_level', function ($row) {
                    $current = $row->levels->where('is_completed', false)->sortBy('level_order')->first();

                    if (!$current) {
                        return '<span class="badge bg-success">Selesai</span>';
                    }

                    $url = route('projects.continue', $row->id);
                    return '<a href="' . $url . '" class="badge bg-primary" style="cursor:pointer;">'
                        . $current->level_name . '</a>';
                })
                ->editColumn('project_name', function ($row) {
                    $url  = route('projects.continue', $row->id);
                    $name = Str::title($row->project_name ?? '-');
                    return '<a href="' . $url . '">' . e($name) . '</a>';
                })
                ->addColumn('action', function ($project) {
                    $buttons = '';
                    if (auth()->user()->can('hapus data proyek')) {
                        $buttons .= '<button data-id="' . $project->id . '"
                                    class="btn btn-icon btn-sm btn-dark delete-projects">
                                    <i class="ti ti-trash"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['current_level', 'action', 'project_type', 'project_name'])
                ->make(true);
        }

        return view('projects.index');
    }

    /**
     * Peta level_name -> method resolver data view.
     * Tambahkan baris baru di sini kalau suatu saat ada project_type dengan
     * step tambahan (mis. "Survei", "Kontrak") — tidak bergantung urutan angka,
     * jadi aman walau jumlah/urutan step beda antar project_type.
     */
    private function levelResolvers(): array
    {
        return [
            'Penawaran Harga' => 'resolvePenawaranData',
            'Invoice'         => 'resolveInvoiceData',
        ];
    }

    private function getCurrentStep($project)
    {
        if (!$project) {
            return 1;
        }

        $current = $project->levels
            ->where('is_completed', false)
            ->sortBy('level_order')
            ->first();

        // level_order + 1 supaya konsisten dgn "step 1 = form project"; kalau semua selesai -> step "selesai"
        return $current ? $current->level_order + 1 : $project->levels->max('level_order') + 2;
    }

    private function computeActiveStep($project, $request = null)
    {
        if ($request && $request->filled('step')) {
            return (int) $request->step;
        }

        return $this->getCurrentStep($project);
    }

    private function buildTimelineSteps($project, int $activeStep): \Illuminate\Support\Collection
    {
        if (!$project) {
            return collect([]);
        }

        return $project->levels
            ->sortBy('level_order')
            ->map(fn ($level) => [
                'id'        => \Illuminate\Support\Str::slug($level->level_name),
                'label'     => $level->level_name,
                'completed' => $level->is_completed,
                'current'   => $activeStep === ($level->level_order + 1),
            ])
            ->values();
    }

    public function create(Request $request)
    {
        $project = null;
        if ($request->has('project_id')) {
            $project = $this->loadBaseProject($request->project_id);
        }

        $activeStep = $this->getCurrentStep($project);
        $canEdit    = auth()->user()->can('lihat daftar proyek');

        $viewData = array_merge([
            'rab'            => null,
            'invoice'        => null,
            'offerItems'     => collect(),
            'groupedItems'   => collect(),
            'totalPenawaran' => 0,
        ], compact('project', 'activeStep', 'canEdit'));

        if ($project) {
            foreach ($this->levelResolvers() as $levelName => $method) {
                $level = $project->levels->firstWhere('level_name', $levelName);

                // Resolve data begitu step utk level itu sudah dimasuki (current atau sudah lewat)
                if ($level && $activeStep >= ($level->level_order + 1)) {
                    $viewData = array_merge($viewData, $this->$method($project));
                }
            }
        }

        $viewData['timelineSteps'] = $this->buildTimelineSteps($project, $activeStep);

        return view('projects.create', array_merge(
            $this->formData($project, $activeStep),
            $viewData
        ));
    }

    private function loadBaseProject($projectId)
    {
        return Project::with([
            'customer.user',
            'employee',
            'projectType',
            'levels',
            'rab.items', // header penawaran + itemnya
            'buildTermins',
            'invoicebuilds',
        ])->findOrFail($projectId);
    }

    /**
     * Data untuk step "Penawaran Harga" — pakai RabProcess (header) + RabProcessItem (baris item),
     * dikelompokkan per category_name. floor_name diabaikan karena wedding tidak punya konsep lantai.
     */
    private function resolvePenawaranData($project): array
    {
        $rab = $project->rab; // RabProcess|null, belum ada sampai item pertama ditambahkan

        $offerItems = $rab
            ? $rab->items()->orderBy('order_no')->get()
            : collect();

        $groupedItems = $offerItems
            ->groupBy(fn ($item) => $item->category_name ?: 'Lainnya')
            ->map(fn ($items, $categoryName) => [
                'category_name' => $categoryName,
                'items'         => $items->values(),
            ])
            ->values();

        $totalPenawaran = $rab->grand_total ?? $offerItems->sum('total');

        return compact('rab', 'offerItems', 'groupedItems', 'totalPenawaran');
    }

    private function resolveInvoiceData($project): array
    {
        $invoiceTermins = Invoice::where('project_id', $project->id)
            ->orderByTermin()
            ->get();

        return compact('invoiceTermins');
    }

    private function formData($project = null, int $activeStep = 1, array $merge = []): array
    {
        $data = [
            'projectStatus' => [
                1 => 'Proses',
                2 => 'Revisi',
                3 => 'Butuh Persetujuan',
                4 => 'Selesai',
            ],
        ];

        if ($activeStep >= 1) {
            $data['employees']    = Employee::with('user:id,fullname')->get(['id', 'user_id']);
            $data['customers']    = Customer::with('user:id,fullname')->get(['id', 'user_id']);
            $data['affiliators']  = Affiliator::with('user:id,fullname')->get(['id', 'user_id']);
            $data['projectTypes'] = ProjectType::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            $data['provinces'] = Province::all();
        }

        // if ($activeStep >= 2) {
        //     // Kalau ada master kategori/paket layanan wedding, ganti query di bawah ini.
        //     // Contoh: $data['itemCategories'] = ServiceCategory::orderBy('name')->get();

        //     // Auto-suggest item dari project lain milik customer yang sama (mirip pola RAB lama)
        //     if ($project?->customer_id) {
        //         $data['pastOfferItems'] = OfferProcessItem::whereHas('rab.project', function ($q) use ($project) {
        //             $q->where('customer_id', $project->customer_id);
        //         })
        //         ->get()
        //         ->unique(fn ($item) => $item->category_name . '|' . $item->job_name . '|' . $item->price)
        //         ->groupBy('category_name');
        //     }
        // }

        return array_merge($data, $merge);
    }

    public function store(ProjectRequest $request)
    {
        abort_if(auth()->user()->cannot('lihat daftar proyek'), 403);

        $project = DB::transaction(function () use ($request) {
            $project = Project::create($request->validated());
            $project->generateLevels(); // pastikan ini sekarang hanya bikin 2 level: Penawaran Harga & Invoice
            return $project;
        });

        $project->load(['employee.user', 'customer.user']);

        $event = 'project_created';
        $cfg   = config("project_events.project_created");

        if (!$cfg) {
            throw new \Exception("Config project_events.$event not found");
        }

        ProjectNotifier::notifyUsers(
            [auth()->user()],
            ProjectNotifier::makePayload($project, [
                'type'    => $event,
                'role'    => 'created_self',
                'title'   => $cfg['title'],
                'message' => $cfg['message']['created_self'],
                'url'     => route('projects.create', ['project_id' => $project->id]),
            ])
        );

        if ($project->employee?->user && $project->employee->user->id !== auth()->id()) {
            ProjectNotifier::notifyUsers(
                [$project->employee->user],
                ProjectNotifier::makePayload($project, [
                    'type'    => $event,
                    'role'    => 'assigned',
                    'title'   => $cfg['title'],
                    'message' => $cfg['message']['assigned'],
                    'url'     => route('projects.create', ['project_id' => $project->id]),
                ])
            );
        }

        $directors = User::role('Direktur')->get();

        ProjectNotifier::notifyUsers(
            $directors,
            ProjectNotifier::makePayload($project, [
                'type'    => $event,
                'role'    => 'director',
                'title'   => $cfg['title'],
                'message' => $cfg['message']['director'],
                'url'     => route('projects.create', ['project_id' => $project->id]),
            ]),
            exceptUserId: auth()->id()
        );

        if ($project->customer?->user) {
            ProjectNotifier::notifyUsers(
                [$project->customer->user],
                ProjectNotifier::makePayload($project, [
                    'type'    => $event,
                    'role'    => 'customer',
                    'title'   => $cfg['title'],
                    'message' => $cfg['message']['customer'],
                    'url'     => route('projects.create', ['project_id' => $project->id]),
                ])
            );
        }

        return redirect()
            ->route('projects.create', ['project_id' => $project->id])
            ->with('success', 'Project berhasil dibuat.');
    }

    public function continue(Project $project, Request $request)
    {
        $project->load([
            'customer.user',
            'employee',
            'levels',
        ]);

        $activeStep = $this->computeActiveStep($project, $request);

        return redirect()->route('projects.create', [
            'project_id' => $project->id,
            'step'       => $activeStep,
        ]);
    }

public function update(Request $request, Project $project)
{
    abort_if(auth()->user()->cannot('lihat daftar proyek'), 403);

    $data = $request->all();

    // Jenis proyek ditangani terpisah (menyelaraskan level), bukan lewat mass update
    $newType = $data['project_type'] ?? null;
    unset($data['project_type']);

    $typeChanged = filled($newType)
        && (string) $newType !== (string) $project->project_type;

    try {
        DB::transaction(function () use ($project, $data, $newType, $typeChanged) {
            if ($typeChanged) {
                // Kunci proyek supaya tidak bentrok dengan proses lain (mis. simpan termin)
                Project::whereKey($project->id)->lockForUpdate()->firstOrFail();

                $project->project_type = $newType;
                $project->syncLevels();
            }

            $project->update($data);
        });
    } catch (\DomainException $e) {
        return back()
            ->withInput()
            ->withErrors(['project_type' => $e->getMessage()])
            ->with('error', $e->getMessage());
    }

    return back()->with('success', 'Data proyek berhasil diperbarui!');
}

    public function show(Project $project)
    {
        return redirect()->route('projects.create', ['project_id' => $project->id]);
    }

    public function destroy(Project $project)
    {
        if ($project) {
            $project->delete();
            return response()->json(['status' => 'success', 'message' => 'Project deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }

        public function generateInvoicesFromTermins(Project $project)
    {
        $project->load('buildTermins');

        if ($project->buildTermins->isEmpty()) {
            return back()->with('error', 'Setting Termin belum diisi, invoice tidak bisa dibuat.');
        }

        if (Invoice::where('project_id', $project->id)->exists()) {
            return back()->with('error', 'Invoice untuk proyek ini sudah pernah dibuat.');
        }

        DB::transaction(function () use ($project) {
            $termins = $project->buildTermins->sortBy('termin_no')->values();

            foreach ($termins as $i => $termin) {
                Invoice::create([
                    'project_id'     => $project->id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'invoice_date'   => now(),
                    'invoice_type'   => Invoice::TYPE_WEDDING,
                    'termin_no'      => $termin->termin_no,
                    'termin_label'   => $termin->description ?: ('Termin ' . $termin->termin_no),
                    'amount'         => $termin->amount,
                    // termin pertama langsung bisa didownload, sisanya nunggu giliran
                    'status'         => $i === 0 ? Invoice::STATUS_WAITING : Invoice::STATUS_DRAFT,
                ]);
            }
        });

        return redirect()
            ->route('projects.create', ['project_id' => $project->id])
            ->with('success', 'Invoice berhasil dibuat dari Setting Termin (' . $project->buildTermins->count() . ' termin).');
    }

    private function generateInvoiceNumber(): string
{
    $year = now()->format('Y');

    $lastInvoice = Invoice::where('invoice_number', 'like', "ZH.I.{$year}.%")
        ->orderByDesc('invoice_number')
        ->first();

    if ($lastInvoice) {
        $lastNumber = (int) substr($lastInvoice->invoice_number, -2);
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return sprintf(
        'ZH.I.%s.%02d',
        $year,
        $nextNumber
    );
}
}