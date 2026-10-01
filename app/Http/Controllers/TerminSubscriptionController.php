<?php

namespace App\Http\Controllers;

use App\Models\BuildTermin;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Simpan sebagai: app/Http/Controllers/TerminSubscriptionController.php
 *
 * Alur satu termin (invoice, bukti pembayaran, approve, dan kwitansi memakai alur lama):
 *   invoice di-approve (= lunas) --(Lanjut)--> termin berikutnya otomatis dibuat
 *                                --(Stop)---> langganan berhenti
 */
class TerminSubscriptionController extends Controller
{
    /**
     * Buat termin ke-1 (admin). Termin berikutnya dibuat otomatis lewat continueSubscription().
     */
    public function store(Request $request, Project $project)
    {
        abort_if($request->user()->cannot('ubah data proyek'), 403);

        $project->loadMissing(['rab', 'levels']);

        $currentLevel = $project->levels->firstWhere('level_name', 'Setting Termin');

        abort_if(! $currentLevel, 404);

        if (! $project->rab) {
            return back()->withErrors([
                'termin' => 'Penawaran Harga belum tersedia.',
            ]);
        }

        // Cek lebih awal: kalau termin sudah ada, tidak perlu validasi input.
        if ($project->buildTermins()->exists()) {
            return back()->withErrors([
                'termin' => 'Termin pertama sudah dibuat.',
            ]);
        }

        $data = $request->validate([
            'amount'             => ['required', 'integer', 'min:1'],
            'billing_period'     => ['required', 'in:monthly,annual'],
            'billing_date'       => ['required', 'date'],
            'termin_description' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.required'       => 'Nominal tagihan wajib diisi.',
            'amount.integer'        => 'Nominal tagihan harus berupa angka bulat (rupiah).',
            'amount.min'            => 'Nominal tagihan harus lebih besar dari 0.',
            'billing_period.in'     => 'Periode berlangganan harus bulanan atau tahunan.',
            'billing_date.required' => 'Tanggal penagihan wajib diisi.',
        ]);

        $offerTotal = (float) $project->rab->grand_total;

        if ($offerTotal <= 0) {
            return back()->withInput()->withErrors([
                'termin' => 'Total penawaran harga belum tersedia. Lengkapi penawaran terlebih dahulu.',
            ]);
        }

        try {
            DB::transaction(function () use ($project, $currentLevel, $data, $offerTotal) {
                // Kunci baris proyek, lalu cek ulang. Mencegah termin ganda
                // kalau tombol simpan terklik dua kali hampir bersamaan.
                Project::whereKey($project->id)->lockForUpdate()->first();

                if (BuildTermin::where('project_id', $project->id)->exists()) {
                    throw new \DomainException('Termin pertama sudah dibuat.');
                }

                $start = Carbon::parse($data['billing_date'])->startOfDay();

                [$periodStart, $periodEnd] = BuildTermin::periodFor($start, $data['billing_period'], 1);

                BuildTermin::create([
                    'project_id'     => $project->id,
                    'termin_no'      => 1,
                    'amount'         => (int) $data['amount'],
                    'percentage'     => $this->percentageOf((float) $data['amount'], $offerTotal),
                    'description'    => $data['termin_description'] ?? null,
                    'billing_period' => $data['billing_period'],
                    'billing_date'   => $start,
                    'period_start'   => $periodStart,
                    'period_end'     => $periodEnd,
                    'status'         => 'unpaid',
                ]);

                // Level "Setting Termin" baru SELESAI saat langganan dihentikan
                // (lihat stopSubscription()), bukan di sini.
                if (! $currentLevel->is_started) {
                    $currentLevel->update(['is_started' => true]);
                }
            });
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['termin' => $e->getMessage()]);
        } catch (\Throwable $e) {
            // DB::transaction sudah rollback otomatis.
            Log::error('Gagal menyimpan termin pertama', [
                'project_id' => $project->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors([
                'termin' => 'Terjadi kesalahan saat menyimpan termin.',
            ]);
        }

        return redirect()
            ->route('projects.create', ['project_id' => $project->id])
            ->with('success', 'Termin ke-1 berhasil dibuat.');
    }

    /**
     * Customer memilih lanjut berlangganan -> termin berikutnya dibuat otomatis.
     */
    public function continueSubscription(Request $request, Project $project, BuildTermin $termin)
    {
        $this->authorizeDecision($request, $project);
        $this->ensureBelongsToProject($project, $termin);

        try {
            $next = DB::transaction(function () use ($project, $termin, $request) {
                $termin = BuildTermin::whereKey($termin->getKey())->lockForUpdate()->firstOrFail();

                $this->assertCanDecide($project, $termin);

                $first = $project->buildTermins()->where('termin_no', 1)->firstOrFail();

                if (! $first->period_start) {
                    throw new \DomainException('Termin ini bukan termin berlangganan.');
                }

                $nextNo = $termin->termin_no + 1;

                [$start, $end] = BuildTermin::periodFor(
                    $first->period_start->copy(),
                    $termin->billing_period,
                    $nextNo
                );

                $termin->update([
                    'renewal_decision' => 'continued',
                    'decided_at'       => now(),
                    'decided_by'       => $request->user()->id,
                ]);

                $offerTotal = (float) ($project->rab?->grand_total ?? 0);

                // Nominal, keterangan, dan satuan periode disalin dari termin sebelumnya
                return $project->buildTermins()->create([
                    'termin_no'      => $nextNo,
                    'amount'         => $termin->amount,
                    'percentage'     => $this->percentageOf((float) $termin->amount, $offerTotal),
                    'description'    => $termin->description,
                    'billing_period' => $termin->billing_period,
                    'billing_date'   => $start,
                    'period_start'   => $start,
                    'period_end'     => $end,
                    'status'         => 'unpaid',
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['termin' => $e->getMessage()]);
        }

        return back()->with('success', "Langganan dilanjutkan. Termin ke-{$next->termin_no} telah dibuat.");
    }

    /**
     * Customer memilih berhenti berlangganan -> status langganan menjadi "Berhenti".
     */
    public function stopSubscription(Request $request, Project $project, BuildTermin $termin)
    {
        $this->authorizeDecision($request, $project);
        $this->ensureBelongsToProject($project, $termin);

        try {
            DB::transaction(function () use ($project, $termin, $request) {
                $termin = BuildTermin::whereKey($termin->getKey())->lockForUpdate()->firstOrFail();

                $this->assertCanDecide($project, $termin);

                $termin->update([
                    'renewal_decision' => 'stopped',
                    'decided_at'       => now(),
                    'decided_by'       => $request->user()->id,
                ]);

                $this->completeSettingTerminLevel($project);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['termin' => $e->getMessage()]);
        }

        return back()->with('success', 'Langganan telah dihentikan.');
    }

    /* =========================================================
     |  Helper
     * ========================================================= */

    /** Termin hanya boleh diputuskan kalau sudah lunas, belum diputuskan, dan paling akhir. */
    private function assertCanDecide(Project $project, BuildTermin $termin): void
    {
        // Lunas = invoice termin ini sudah di-approve (alur invoice yang sudah ada)
        $invoice = $project->invoicebuilds()->where('termin', $termin->termin_no)->first();

        if (! $invoice || $invoice->status !== 'approved') {
            throw new \DomainException('Termin ini belum lunas. Invoice harus di-approve terlebih dahulu.');
        }

        if ($termin->renewal_decision !== null) {
            throw new \DomainException('Keputusan untuk termin ini sudah dipilih.');
        }

        if ($project->buildTermins()->where('termin_no', '>', $termin->termin_no)->exists()) {
            throw new \DomainException('Sudah ada termin yang lebih baru.');
        }
    }

    /**
     * Langganan berhenti = proses termin selesai, jadi level "Setting Termin" ditandai selesai.
     * Hanya menandai selesai; perpindahan ke level berikutnya tidak diubah di sini.
     */
    private function completeSettingTerminLevel(Project $project): void
    {
        $level = $project->levels()->where('level_name', 'Setting Termin')->first();

        if ($level && ! $level->is_completed) {
            $level->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }
    }

    private function percentageOf(float $amount, float $offerTotal): float
    {
        return $offerTotal > 0 ? round($amount / $offerTotal * 100, 4) : 0;
    }

    private function ensureBelongsToProject(Project $project, BuildTermin $termin): void
    {
        abort_unless((string) $termin->project_id === (string) $project->id, 404);
    }

    private function authorizeManage(Request $request): void
    {
        abort_if($request->user()->cannot('ubah data proyek'), 403);
    }

    /** Boleh memutuskan lanjut/stop: admin, atau customer pemilik proyek. */
    private function authorizeDecision(Request $request, Project $project): void
    {
        $user = $request->user();
        $isCustomer = $project->customer?->user?->id === $user->id;

        abort_unless($isCustomer || $user->can('ubah data proyek'), 403);
    }
}