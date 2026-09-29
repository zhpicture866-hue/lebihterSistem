<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasUuid;
    
    public const STATUS_ACTIVE          = 'active';
    public const STATUS_SEGERA_BERAKHIR = 'segera_berakhir';
    public const STATUS_EXPIRED         = 'expired';
    public const STATUS_STOPPED         = 'stopped';

    protected $fillable = [
        'system_id',
        'plan_id',
        'customer_id',
        'status',
        'start_date',
        'end_date',
        'auto_renew',
        'payment_method_id',
        'last_notified_stage',
        'last_notified_at',
        'stopped_at',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'auto_renew'       => 'boolean',
        'last_notified_at' => 'datetime',
        'stopped_at'       => 'datetime',
    ];
    protected $table = 'lebihtersistem.subscriptions';
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /** Langganan yang sudah lewat end_date tapi belum ditandai expired/stopped. */
    public function scopeNeedsExpiring(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_SEGERA_BERAKHIR])
            ->whereDate('end_date', '<', Carbon::today());
    }

    /** Langganan yang mendekati end_date dalam $days hari ke depan, masih aktif. */
    public function scopeExpiringWithin(Builder $query, int $days): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereDate('end_date', '<=', Carbon::today()->addDays($days))
            ->whereDate('end_date', '>=', Carbon::today());
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    /** Perpanjang langganan setelah invoice-nya dibayar. */
    public function extend(): void
    {
        $this->update([
            'status'               => self::STATUS_ACTIVE,
            'end_date'             => $this->end_date->isPast()
                ? Carbon::today()->addDays($this->plan->duration_days)
                : $this->end_date->addDays($this->plan->duration_days),
            'last_notified_stage'  => null,
            'last_notified_at'     => null,
        ]);
    }
}
