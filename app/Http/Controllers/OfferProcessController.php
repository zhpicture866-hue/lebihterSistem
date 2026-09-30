<?php

namespace App\Http\Controllers;

use App\Models\OfferProcess;
use App\Models\OfferProcessItem;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\ProjectNotifier;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferProcessController extends Controller
{

public function store(Request $request)
{
    // Kalau gagal, Laravel otomatis redirect back + errors + old input
    $validated = $this->validateOffer($request);

    $project = Project::with('customer.user')->findOrFail($validated['project_id']);
    $calc    = $this->calculateOffer($validated);

    DB::beginTransaction();

    try {
        $offerProcess = OfferProcess::create($calc['header'] + [
            'project_id'   => $project->id,
            'offer_number' => $this->generateOfferNumber(),
            'offer_date'   => $validated['offer_date'],
            'contact_name' => $project->customer->user->fullname ?? '',
            'notes'        => $validated['notes'] ?? null,
            'created_by'   => auth()->id(),
            'updated_by'   => auth()->id(),
        ]);

        $offerProcess->items()->createMany($calc['items']);

        $currentLevel = $project->levels()
            ->where('level_order', 1)
            ->first();

        $nextLevel = $project->levels()
            ->where('level_order', '>', 2)
            ->orderBy('level_order')
            ->first();

        if ($currentLevel && ! $currentLevel->is_completed) {
            $currentLevel->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        if ($nextLevel) {
            $nextLevel->update([
                'is_started' => true,
                'started_at' => $nextLevel->started_at ?? now(),
            ]);

            $project->update([
                'active_step' => $nextLevel->level_order,
            ]);
        } else {
            $project->update([
                'active_step' => $currentLevel?->level_order ?? 2,
            ]);
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        report($e);

        return back()
            ->withInput()
            ->with('error', 'Gagal menyimpan penawaran. Silakan coba lagi.');
    }

    // Di luar transaction: notifikasi gagal tidak membatalkan penawaran
    try {
        $this->notifyProjectEvent($project, 'rab_created');
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()
        ->back()
        ->with('success', 'Form Penawaran Harga berhasil disimpan.');
}

private function validateOffer(Request $request, bool $isUpdate = false): array
{
    $rules = [
        'offer_date'             => ['required', 'date'],
        'discount'               => ['nullable', 'numeric', 'min:0'],
        'tax_rate'               => ['nullable', 'numeric', 'min:0', 'max:100'],
        'shipping'               => ['nullable', 'numeric', 'min:0'],
        'profit'                 => ['nullable', 'numeric', 'min:0'],
        'overhead'               => ['nullable', 'numeric', 'min:0'],
        'notes'                  => ['nullable', 'string'],

        'items'                  => ['required', 'array', 'min:1'],
        'items.*.description'    => ['nullable', 'string'],
        'items.*.billing_period' => ['required', 'in:monthly,annual'],
        'items.*.volume'         => ['required', 'numeric', 'gt:0'],
        'items.*.base_price'     => ['required', 'numeric', 'min:0'],
    ];

    if (! $isUpdate) {
        $rules['project_id'] = ['required', 'uuid', Rule::exists(Project::class, 'id')];
    }

    return $request->validate($rules, [
        'items.required'            => 'Minimal harus ada 1 item penawaran.',
        'items.min'                 => 'Minimal harus ada 1 item penawaran.',
        'items.*.volume.gt'         => 'Qty harus lebih besar dari 0.',
        'items.*.billing_period.in' => 'Periode berlangganan harus bulanan atau tahunan.',
    ]);
}

/**
 * Semua angka dihitung ulang di server. Nilai subtotal, tax_total, grand_total,
 * price, dan total yang dikirim browser tidak dipakai sama sekali.
 */
private function calculateOffer(array $data): array
{
    $profitRate   = (float) ($data['profit'] ?? 0);
    $overheadRate = (float) ($data['overhead'] ?? 0);
    $taxRate      = (float) ($data['tax_rate'] ?? 0);
    $shipping     = (float) ($data['shipping'] ?? 0);

    $rows         = [];
    $baseSubtotal = 0;
    $subtotal     = 0;

    foreach (array_values($data['items']) as $index => $item) {
        $volume    = (float) $item['volume'];
        $basePrice = (float) $item['base_price'];

        $price = round($basePrice * (1 + ($overheadRate + $profitRate) / 100), 2);
        $total = round($volume * $price, 2);

        $baseSubtotal += $volume * $basePrice;
        $subtotal     += $total;

        $rows[] = [
            'description'    => $this->cleanDescription($item['description'] ?? null),
            'billing_period' => $item['billing_period'],
            'volume'         => $volume,
            'base_price'     => $basePrice,
            'price'          => $price,
            'total'          => $total,
            'profit'         => $profitRate,
            'overhead'       => $overheadRate,
            'is_draft'       => false,
            'order_no'       => $index + 1,
        ];
    }

    $discount              = min((float) ($data['discount'] ?? 0), $subtotal);
    $subtotalAfterDiscount = $subtotal - $discount;
    $taxTotal              = round($subtotalAfterDiscount * $taxRate / 100, 2);
    $grandTotal            = $subtotalAfterDiscount + $taxTotal + $shipping;

    return [
        'header' => [
            'base_subtotal'           => round($baseSubtotal, 2),
            'subtotal'                => round($subtotal, 2),
            'discount'                => round($discount, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'tax_rate'                => $taxRate,
            'tax_total'               => $taxTotal,
            'shipping'                => $shipping,
            'overhead'                => $overheadRate,
            'profit'                  => $profitRate,
            'grand_total'             => round($grandTotal, 2),
        ],
        'items' => $rows,
    ];
}

private function cleanDescription(?string $html): ?string
{
    if ($html === null || trim(strip_tags($html)) === '') {
        return null; // Quill kosong menghasilkan "<p><br></p>"
    }

    return strip_tags($html, '<p><br><strong><em><u><ol><ul><li><a>');
}

    protected function notifyProjectEvent(Project $project, string $event)
{
    $cfg = config("project_events.$event");
    if (!$cfg) return;

    $admin    = auth()->user();
    $customer = $project->customer?->user;

    $targets = [];

    if ($admin) {
        $targets['admin'] = $admin;
    }

    if ($customer) {
        $targets['customer'] = $customer;
    }

    foreach ($targets as $role => $user) {
        if (!isset($cfg['message'][$role])) continue;

        ProjectNotifier::notifyUsers(
            [$user],
            ProjectNotifier::makePayload($project, [
                'type'    => $event,
                'role'    => $role,
                'title'   => $cfg['title'],
                'message' => $cfg['message'][$role],
                'url'     => route('projects.create', ['project_id' => $project->id]),
            ])
        );
    }
}
private function generateOfferNumber(): string
{
    $year = now()->format('Y');

    $lastOffer = OfferProcess::query()
        ->whereYear('offer_date', $year)
        ->where('offer_number', 'like', "ZH.Q.{$year}.%")
        ->orderByDesc('id')
        ->first();

    if ($lastOffer) {

        $lastNumber = (int) substr(
            $lastOffer->offer_number,
            strrpos($lastOffer->offer_number, '.') + 1
        );

        $number = $lastNumber + 1;

    } else {

        $number = 1;
    }

    return sprintf(
        'ZH.Q.%s.%02d',
        $year,
        $number
    );
}

public function exportPdf(Project $project)
{
    $offer = $project->rab()
        ->with([
            'items' => fn ($q) => $q
                ->orderBy('order_no')
                ->orderBy('id')
        ])
        ->latest('id')
        ->first();

    if (!$offer) {
        abort(404);
    }

    $payments = $project->buildTermins()
        ->orderBy('termin_no')
        ->get();

    $pdf = Pdf::loadView('rab.pdf', compact(
        'offer',
        'project',
        'payments'
    ))->setPaper('A4', 'portrait');

    // Nama customer
    $customerName = $project->customer?->user?->fullname ?? 'CUSTOMER';

    // Kota proyek
    $cityName = $project->project_location ?? 'KOTA';

    // Bersihkan karakter yang tidak boleh digunakan pada nama file
    $clean = function ($value) {
        return trim(
            preg_replace('/[\\\\\/:*?"<>|]+/', '-', $value)
        );
    };

    $filename =
        $clean($offer->offer_number)
        . ' '
        . $clean($project->project_name)
        . ' - '
        . $clean($customerName)
        . ' - '
        . $clean($cityName)
        . '.pdf';

    return $pdf->stream($filename);
}
public function structure($id)
{
    $rab = OfferProcess::with([
        'items',
    ])->findOrFail($id);

    return response()->json([
        'meta' => [
            'profit' => $rab->profit,
            'overhead' => $rab->overhead,
            'discount' => $rab->discount,
            'tax_rate' => $rab->tax_rate,
            'shipping' => $rab->shipping,
        ],
        'items' => $rab->items
    ]);
}

public function update(Request $request, string $project, OfferProcess $rab)
{
    // pastikan penawaran memang milik proyek di URL
    abort_unless($rab->project_id === $project, 404);

    $data = $this->validateOffer($request, isUpdate: true);
    $calc = $this->calculateOffer($data);

    DB::transaction(function () use ($rab, $data, $calc, $request) {
        $rab = OfferProcess::whereKey($rab->getKey())->lockForUpdate()->firstOrFail();

        $rab->update($calc['header'] + [
            'offer_date' => $data['offer_date'],
            'notes'      => $data['notes'] ?? null,
            'updated_by' => $request->user()->id,
        ]);

        $rab->items()->delete();
        $rab->items()->createMany($calc['items']);
    });

    return redirect()->back()
        ->with('success', "Penawaran {$rab->offer_number} berhasil diperbarui.");
}
}