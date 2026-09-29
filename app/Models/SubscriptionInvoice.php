<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasUuid;
    
    public const STATUS_DRAFT   = 'draft';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_PAID    = 'paid';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'subscription_id',
        'invoice_number',
        'amount',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];
    protected $table = 'lebihtersistem.subscription_invoices';
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
