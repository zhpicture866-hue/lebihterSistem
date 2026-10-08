<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;

class Permission extends SpatiePermission
{
    use HasUuid;

    protected $guarded = [];
    public const GUARD = 'sistem_b';

    protected static function booted(): void
    {
        static::addGlobalScope('guard', function (Builder $q) {
            $q->where($q->getModel()->getTable().'.guard_name', self::GUARD);
        });

        static::creating(function ($model) {
            $model->guard_name = self::GUARD;
        });
    }
}
