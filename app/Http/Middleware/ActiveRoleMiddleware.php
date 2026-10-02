<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\RoleHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next, $roleName): Response
    // {
    //     $active = auth()->user()->activeRole?->name;

    //     if ($active !== $roleName) {
    //         abort(403, 'Akses ditolak. Role aktif tidak sesuai.');
    //     }

    //     return $next($request);
    // }

public function handle($request, Closure $next, $roleNames)
{
    if (!auth()->check()) {
        abort(403, 'Unauthorized.');
    }

    $user = auth()->user();

    if ($user->hasAnyRole([
        'Super-Admin',
        'Direktur',
        'Manager Marketing',
        'Manager Operasional'
    ])) {
        return $next($request);
    }

    if (!$user->activeRole) {
        abort(403, 'Tidak ada active role yang dipilih.');
    }

    $allowed = collect($roleNames)
        ->map(fn($r) => strtolower(trim($r)));

    if (!$allowed->contains(strtolower($user->activeRole->name))) {
        abort(403, 'Role aktif tidak sesuai.');
    }

    return $next($request);
}
}
