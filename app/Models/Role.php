<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{
    use HasUuid;

    public const GUARD = 'sistem_b';
    protected $keyType = 'string';
    public $incrementing = false;

        public function getRouteKeyName()
    {
        return 'id'; 
    }

    protected $fillable = [
        'name',
        'guard_name',
        'role_group',
    ];

        protected static function booted(): void
    {
        static::addGlobalScope('guard', function (Builder $q) {
            $q->where($q->getModel()->getTable().'.guard_name', self::GUARD);
        });

        static::creating(function ($model) {
            $model->guard_name = self::GUARD;
        });
    }

        public function scopeInternal($query)
    {
        return $query->where('role_group', 'Internal');
    }

    public function scopeExternal($query)
    {
        return $query->where('role_group', 'Eksternal');
    }
}
