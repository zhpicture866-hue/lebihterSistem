<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasUuid;
    
    protected $table = 'lebihtersistem.plans';
    protected $fillable = ['system_id', 'name', 'price', 'duration_days', 'is_active'];

    protected $casts = [
        'price'      => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
