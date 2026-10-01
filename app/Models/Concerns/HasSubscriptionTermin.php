<?php

namespace App\Models\Concerns;

use Carbon\Carbon;

/**
 * Simpan sebagai: app/Models/Concerns/HasSubscriptionTermin.php
 *
 * Pasang di model BuildTermin:
 *
 *     use App\Models\Concerns\HasSubscriptionTermin;
 *
 *     class BuildTermin extends Model
 *     {
 *         use HasSubscriptionTermin;
 *         ...
 *     }
 *
 * Lalu tambahkan kolom baru ke $fillable BuildTermin:
 *     'billing_period', 'period_start', 'period_end',
 *     'renewal_decision', 'decided_at', 'decided_by'
 */
trait HasSubscriptionTermin
{
    public function initializeHasSubscriptionTermin(): void
    {
        $this->mergeCasts([
            'billing_date' => 'date',
            'period_start' => 'date',
            'period_end'   => 'date',
            'decided_at'   => 'datetime',
        ]);
    }

    /**
     * Masa layanan untuk termin ke-$number, dihitung dari tanggal awal (termin 1).
     * Dihitung dari anchor (bukan dari akhir termin sebelumnya) supaya tanggal tidak
     * "bergeser" -- mis. langganan yang mulai tanggal 31 tidak menjadi tanggal 30 selamanya.
     *
     * @return array{0: Carbon, 1: Carbon} [period_start, period_end]
     */
    public static function periodFor(Carbon $anchor, string $unit, int $number): array
    {
        $shift = fn (int $k) => $unit === 'annual'
            ? $anchor->copy()->addYearsNoOverflow($k)
            : $anchor->copy()->addMonthsNoOverflow($k);

        return [$shift($number - 1), $shift($number)->subDay()];
    }

    public function getBillingPeriodLabelAttribute(): string
    {
        return $this->billing_period === 'annual' ? 'Tahunan' : 'Bulanan';
    }
}