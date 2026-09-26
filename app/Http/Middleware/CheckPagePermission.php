<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    public function handle(Request $request, Closure $next, $pageKey = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Jika role adalah Admin, berikan akses penuh
        if (strtolower($user->role ?? '') === 'admin') {
            return $next($request);
        }

        // Jika $pageKey kosong/tidak dikirim dari route, langsung lewatkan
        if (empty($pageKey)) {
            return $next($request);
        }

        // 2. Pengecekan permissions user
        $permissions = $user->permissions;

        if (is_null($permissions)) {
            $hasAccess = true;
        } else {
            $hasAccess = is_array($permissions) && in_array($pageKey, $permissions);
        }

        if ($hasAccess) {
            return $next($request);
        }

        return redirect()->route('dashboard')
            ->with('error', 'Akses ditolak! Halaman ini telah dinonaktifkan oleh Administrator.');
    }
}
