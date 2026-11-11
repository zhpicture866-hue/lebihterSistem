<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class ActiveRole
{
    public static function role()
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        $active = $user->activeRole;

        // Pakai peran aktif hanya kalau masih dimiliki user
        if ($active && $user->roles->contains('id', $active->id)) {
            return $active;
        }

        return $user->roles->first();
    }

    public static function name()
    {
        return self::role()?->name;
    }

    public static function permissions()
    {
        return self::role()?->permissions->pluck('name')->toArray() ?? [];
    }


    public static function hasPermission($permission)
    {
        return in_array($permission, self::permissions());
    }
}