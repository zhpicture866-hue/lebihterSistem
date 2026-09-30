<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferProcessItem extends Model
{
    protected $table = 'lebihtersistem.offer_process_items';

    protected $fillable = [
        'offer_process_id',
        'volume',
        'base_price',
        'price',
        'total',
        'profit',
        'overhead',
        'description',
        'is_draft',
        'order_no',
        'billing_period',
    ];

    protected $casts = [
        'volume' => 'decimal:5',
        'base_price' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'profit' => 'decimal:2',
        'overhead' => 'decimal:2',
        'is_draft' => 'boolean',
        'order_no' => 'integer',
    ];

    public function rab()
    {
        return $this->belongsTo(OfferProcess::class, 'offer_process_id');
    }

public function getBillingPeriodLabelAttribute(): string
{
    return $this->billing_period === 'annual' ? 'Tahunan' : 'Bulanan';
}
}