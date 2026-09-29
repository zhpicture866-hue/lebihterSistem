<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BuildTermin extends Model
{
    use HasUuids;

    protected $table = 'zhpicture.build_termins';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'termin_no',
        'percentage',
        'amount',
        'description',
        'billing_date',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
        'billing_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}