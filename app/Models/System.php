<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class System extends Model
{
    use HasUuid;
    
    protected $table = 'lebihtersistem.systems';
    protected $fillable = ['name', 'slug', 'base_url', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
