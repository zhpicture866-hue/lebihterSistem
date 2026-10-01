<?php

namespace App\Models;

use App\Models\Concerns\HasSubscriptionTermin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BuildTermin extends Model
{
    use HasUuids, HasSubscriptionTermin;

    protected $table = 'lebihtersistem.build_termins';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'termin_no',
        'percentage',
        'amount',
        'description',
        'billing_date',
        'billing_period', 'period_start', 'period_end',
        'renewal_decision', 'decided_at', 'decided_by'
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}