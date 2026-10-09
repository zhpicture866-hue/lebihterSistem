<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\InvoiceBuild;
use App\Models\ProjectLevel;
use App\Models\BuildProcessItem;
use App\Models\BuildPlans;
use App\Models\BuildWeeklyProgress;
use App\Services\ProjectNotifier;
use App\Services\InvoiceBuildNumberGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

class InvoiceBuildController extends Controller
{

public function invoiceBuild(Project $project, int $termin, ?string $filename = null)
{
    abort_if(!$project->rab, 404);

    Carbon::setLocale('id');
    $buildTermin = $project->buildTermins()
        ->where('termin_no', $termin)
        ->first();

    abort_if(
        !$buildTermin,
        404,
        'Termin Build tidak ditemukan.'
    );

    $offer = $project->rab;

    $grandTotal = (float) $offer->grand_total;

    $result = DB::transaction(function () use (
        $project,
        $termin,
        $buildTermin,
        $grandTotal
    ) {

        $paymentPercentage = (float) $buildTermin->percentage;
        $newAmount = (float) $buildTermin->amount;
        $termins = $project->buildTermins()
            ->orderBy('termin_no')
            ->get();

        // $progressStart = 0;

        // foreach ($termins as $item) {

        //     if ((int) $item->termin_no === $termin) {
        //         break;
        //     }

        //     $progressStart += (float) $item->percentage;
        // }

        // $progressEnd = $progressStart + $paymentPercentage;

        $invoice = InvoiceBuild::where('project_id', $project->id)
            ->where('termin', $termin)
            ->lockForUpdate()
            ->first();

        $justCreated = false;

        if (!$invoice) {

            $invoice = InvoiceBuild::create([
                'project_id'         => $project->id,
                'invoice_type'       => InvoiceBuild::TYPE_WEDDING,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date'       => now(),
                'termin'             => $termin,
                // 'progress_start'     => $progressStart,
                // 'progress_end'       => $progressEnd,
                'payment_percentage' => $paymentPercentage,
                'amount'             => $newAmount,
                'status'             => 'waiting',
            ]);

            $justCreated = true;

        } else {

            if (
                (float) $invoice->amount !== $newAmount ||
                (float) $invoice->payment_percentage !== $paymentPercentage
                // (float) $invoice->progress_start !== $progressStart ||
                // (float) $invoice->progress_end !== $progressEnd
            ) {

                $invoice->update([
                    'amount'             => $newAmount,
                    'payment_percentage' => $paymentPercentage,
                    // 'progress_start'     => $progressStart,
                    // 'progress_end'       => $progressEnd,
                ]);
            }
        }

        if (!$invoice->downloaded_at) {

            $invoice->update([
                'downloaded_at' => now(),
            ]);
        }

        return [
            'invoice'     => $invoice->fresh(),
            'grandTotal'  => $grandTotal,
            'justCreated' => $justCreated,
        ];
    });

    if ($result['justCreated']) {
        $this->notifyInvoiceBuildCreated($project, $result['invoice']);
    }
    $clean = function ($value) {
        return trim(
            preg_replace('/[\\\\\/:*?"<>|]+/', '-', (string) $value)
        );
    };
    $newFilename =
        $clean($result['invoice']->invoice_number)
        . '-' . $clean($project->projectType?->name)
        . '-' . $clean($project->project_name)
        . '.pdf';

    // Kalau URL belum memuat nama file, arahkan ke URL yang berakhir dengan nama file
    if ($filename === null) {
        return redirect()->route('projects.invoice.build', [
            'project'  => $project,
            'termin'   => $termin,
            'filename' => $newFilename,
        ]);
    }
    return Pdf::loadView('invoice.build', [
        'invoice'    => $result['invoice'],
        'project'    => $project,
        'offer'      => $offer,
        'grandTotal' => $result['grandTotal'],
    ])
    ->setPaper('A4', 'portrait')
    ->stream($newFilename);
}

/**
 * Beri tahu customer & admin bahwa invoice termin baru siap dibayar.
 * Dipanggil HANYA saat baris InvoiceBuild pertama kali dibuat.
 */
private function notifyInvoiceBuildCreated(Project $project, InvoiceBuild $invoice): void
{
    $event = 'invoice_build_created';

    $cfg = config("project_events.$event");

    if (!$cfg) {
        throw new \Exception("Config project_events.$event not found");
    }

    $payloadExtra = [
        'termin'         => $invoice->termin,
        'amount'         => number_format($invoice->amount, 0, ',', '.'),
        'progress_start' => $invoice->progress_start,
        'progress_end'   => $invoice->progress_end,
    ];

    // Aksi ini bisa dipicu customer sendiri (klik download invoice untuk
    // pertama kalinya), jadi auth()->user() TIDAK boleh dipakai sebagai
    // fallback penerima notifikasi admin — itu akan mengirim notifikasi
    // ke diri sendiri (si customer).
    $actorId         = auth()->id();
    $isCustomerActor = $project->customer?->user_id === $actorId;

    $staffRecipients = collect([
        $project->employee?->user,
        $project->createdBy,
    ])->filter()->unique('id');

    // Kalau proyek belum punya PIC/pembuat yang jelas, jatuhkan ke semua
    // staf dengan role "Tim" (pola yang sama dipakai di store()).
    if ($staffRecipients->isEmpty()) {
        $staffRecipients = User::role('Manager Operasional')->get();
    }

    ProjectNotifier::notifyUsers(
        $staffRecipients,
        ProjectNotifier::makePayload($project, [
            'type'    => $event,
            'role'    => 'Super-Admin',
            'title'   => ProjectNotifier::parseMessage($cfg['title'], $payloadExtra),
            'message' => ProjectNotifier::parseMessage($cfg['message']['Super-Admin'], $payloadExtra),
            'url'     => route('projects.create', ['project_id' => $project->id]),
        ]),
        // Kalau admin sendiri yang men-trigger, jangan kirim notifikasi
        // ini balik ke dirinya sendiri.
        exceptUserId: $isCustomerActor ? null : $actorId
    );

    // Customer hanya diberi tahu kalau BUKAN dia sendiri yang baru saja
    // men-download (mis. admin yang men-generate invoice-nya).
    if ($project->customer?->user && ! $isCustomerActor) {
        ProjectNotifier::notifyUsers(
            [$project->customer->user],
            ProjectNotifier::makePayload($project, [
                'type'    => $event,
                'role'    => 'Customer',
                'title'   => ProjectNotifier::parseMessage($cfg['title'], $payloadExtra),
                'message' => ProjectNotifier::parseMessage($cfg['message']['customer'], $payloadExtra),
                'url'     => route('projects.create', ['project_id' => $project->id]),
            ])
        );
    }
}

public function approve(Project $project, InvoiceBuild $invoice)
{
    abort_if(
        ! auth()->user()->hasAnyRole(['Super-Admin', 'Employee']),
        403
    );
    abort_if(
        $invoice->project_id !== $project->id,
        404
    );

    // Authorization
    if (
        $project->customer?->user_id !== auth()->id()
        && auth()->user()->cannot('lihat daftar proyek')
    ) {
        abort(403);
    }

    if (!$invoice->downloaded_at) {
        return back()->with(
            'error',
            'Invoice belum didownload.'
        );
    }

    if ($invoice->approved_at) {
        return back()->with(
            'info',
            'Invoice sudah disetujui.'
        );
    }

    $currentTermin = (int) $invoice->termin;

    if ($currentTermin > 1) {

        $previousInvoice = InvoiceBuild::where('project_id', $project->id)
            ->where('termin', $currentTermin - 1)
            ->first();

        abort_if(
            !$previousInvoice || !$previousInvoice->approved_at,
            403,
            'Termin sebelumnya belum disetujui.'
        );
    }

    DB::transaction(function () use (
        $project,
        $invoice,
        $currentTermin
    ) {

        $invoice->update([
            'status'         => 'approved',
            'approved_at'    => now(),
            'approve_by_name' => auth()->user()->fullname ?? 'Customer',
            'approved_ip'    => request()->ip(),
        ]);

        $lastTermin = (int) $project->buildTermins()->max('termin_no');

        if ($currentTermin === $lastTermin) {
            $project->levels()
                ->whereIn('level_name', ['Setting Termin', 'Invoice'])
                ->where('is_completed', false)
                ->update([
                    'is_completed' => true,
                ]);
        }
    });

    // Event notifikasi untuk approval, BUKAN 'invoice_build_created' —
    // event itu untuk saat invoice pertama kali tersedia/bisa didownload,
    // dan seharusnya dikirim dari tempat invoice itu dibuat, bukan di sini.
    $event = 'invoice_build_approved';

    $cfg = config("project_events.$event");

    if (!$cfg) {
        throw new \Exception(
            "Config project_events.$event not found"
        );
    }

    $payloadExtra = [
        'termin' => $invoice->termin,

        'amount' => number_format(
            $invoice->amount,
            0,
            ',',
            '.'
        ),

        'progress_start' => $invoice->progress_start,

        'progress_end' => $invoice->progress_end,
    ];


    ProjectNotifier::notifyUsers(
        [
            $project->createdBy
                ?? auth()->user()
        ],
        ProjectNotifier::makePayload(
            $project,
            [
                'type' => $event,

                'role' => 'Super-Admin',

                'title' => ProjectNotifier::parseMessage(
                    $cfg['title'],
                    $payloadExtra
                ),

                'message' => ProjectNotifier::parseMessage(
                    $cfg['message']['Super-Admin'],
                    $payloadExtra
                ),

                'url' => route(
                    'projects.create',
                    [
                        'project_id' => $project->id
                    ]
                ),
            ]
        )
    );


    if ($project->customer?->user) {

        ProjectNotifier::notifyUsers(
            [
                $project->customer->user
            ],
            ProjectNotifier::makePayload(
                $project,
                [
                    'type' => $event,

                    'role' => 'Customer',

                    'title' => ProjectNotifier::parseMessage(
                        $cfg['title'],
                        $payloadExtra
                    ),

                    'message' => ProjectNotifier::parseMessage(
                        $cfg['message']['customer'],
                        $payloadExtra
                    ),

                    'url' => route(
                        'projects.create',
                        [
                            'project_id' => $project->id
                        ]
                    ),
                ]
            )
        );
    }


    return redirect()
        ->route(
            'projects.create',
            [
                'project_id' => $project->id
            ]
        )
        ->with(
            'success',
            "Invoice Termin {$invoice->termin} berhasil disetujui."
        );
}

public function downloadKwitansi(Project $project, InvoiceBuild $invoice, Request $request, ?string $filename = null)
{
    abort_if($invoice->project_id !== $project->id, 404);
    abort_if($invoice->status !== InvoiceBuild::STATUS_APPROVED, 403);
 
    $regenerate = $request->boolean('regenerate')
        && auth()->user()->hasAnyRole(['Super-Admin', 'Manager Operasional']);

    if (! $regenerate
        && $invoice->kwitansi_path
        && Storage::disk('local')->exists($invoice->kwitansi_path)) {
        return $this->respondKwitansi($project, $invoice, $filename);
    }
 
    DB::transaction(function () use ($invoice, $regenerate) {
 
        // Kunci baris invoice, lalu baca ulang datanya.
        $invoice = InvoiceBuild::whereKey($invoice->id)->lockForUpdate()->first();
 
        if (! $regenerate
            && $invoice->kwitansi_path
            && Storage::disk('local')->exists($invoice->kwitansi_path)) {
            return;
        }
 
        $number = $invoice->kwitansi_number ?: $this->generateKwitansiNumber();
 
        $project = $invoice->project()->with('customer.user', 'rab', 'buildTermins')->first();
        $offer   = $project->rab;
 
        $lastTermin = (int) $project->buildTermins->max('termin_no');
 
        $pdf = Pdf::loadView('invoice.kwitansi-pdf', [
            'invoice'        => $invoice,
            'project'        => $project,
            'offer'          => $offer,
            'grandTotal'     => $offer->grand_total ?? 0,
            'number'         => $number,
            'isFinalPayment' => (int) $invoice->termin === $lastTermin,
        ]);
 
        $path = 'kwitansi/' . $invoice->id . '.pdf';
 
        Storage::disk('local')->put($path, $pdf->output());
 
        $invoice->update([
            'kwitansi_number'       => $number,
            'kwitansi_path'         => $path,
            'kwitansi_generated_at' => now(),
        ]);
    });
 
    return $this->respondKwitansi($project, $invoice->fresh(), $filename);
}
 
    private function respondKwitansi(Project $project, InvoiceBuild $invoice, ?string $filename)
{
    $namaFile = $this->kwitansiFilename($invoice);

    // URL belum memuat nama file: arahkan ke URL yang berakhir dengan nama file.
    // Query string (mis. regenerate) sengaja tidak dibawa.
    if ($filename === null) {
        return redirect()->route('projects.invoice.build.kwitansi', [
            'project'  => $project,
            'invoice'  => $invoice,
            'filename' => $namaFile,
        ]);
    }

    return Storage::disk('local')->response(
        $invoice->kwitansi_path,
        $namaFile
    );
}

    private function kwitansiFilename(InvoiceBuild $invoice): string
{
    $project = $invoice->project;

    // Bersihkan karakter yang tidak boleh digunakan pada nama file
    $clean = fn ($value) => trim(
        preg_replace('/[\\\\\/:*?"<>|]+/', '-', (string) $value)
    );

    return $clean($invoice->kwitansi_number)
        . '-' . $clean($project->projectType?->name)
        . '-' . $clean($project->project_name)
        . '.pdf';
}
 
private function generateKwitansiNumber(): string
{
    $year = now()->format('Y');
 
    $lastNumber = InvoiceBuild::where('kwitansi_number', 'like', "ZH.K.{$year}.%")
        ->orderByDesc('kwitansi_number')
        ->value('kwitansi_number');
 
    $next = $lastNumber
        ? ((int) substr($lastNumber, -2)) + 1
        : 1;
 
    return sprintf('ZH.K.%s.%02d', $year, $next);
}
 
    private function generateInvoiceNumber(): string
{
    $year = now()->format('Y');

    $lastInvoice = InvoiceBuild::where('invoice_number', 'like', "ZH.I.{$year}.%")
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

public function uploadBuktiPembayaran(Request $request, Project $project, InvoiceBuild $invoicebuild)
{
    abort_if(
        auth()->user()->cannot('lihat data proyek'),
        403
    );

    abort_if($invoicebuild->project_id !== $project->id, 404);

    // Upload hanya boleh setelah invoice di-download.
    abort_if(! $invoicebuild->downloaded_at, 422, 'Invoice belum di-download.');

    $request->validate([
        'bukti_pembayaran' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
    ]);

    if ($invoicebuild->bukti_pembayaran) {
        Storage::disk('public')->delete($invoicebuild->bukti_pembayaran);
    }

    $path = $request->file('bukti_pembayaran')->store(
        'invoice-build/bukti-pembayaran',
        'public'
    );

    $invoicebuild->update([
        'bukti_pembayaran' => $path,
        'bukti_pembayaran_uploaded_at' => now(),
    ]);

    $buildTermin = $project->buildTermins()
        ->where('termin_no', $invoicebuild->termin)
        ->first();

    $terminLabel = $buildTermin->description ?? "Termin {$invoicebuild->termin}";

    return back()->with('success', "Bukti pembayaran {$terminLabel} berhasil diunggah.");
}
}