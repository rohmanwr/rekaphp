<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPagePermission
{
    public function handle(Request $request, Closure $next, $pageKey): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Jika role adalah Admin, berikan akses penuh tanpa syarat
        if (strtolower($user->role) === 'admin') {
            return $next($request);
        }

        // 2. Ambil data permissions dari user
        $permissions = $user->permissions;

        // PENTING: Jika permissions bernilai NULL (artinya user baru dan belum pernah disimpan pengaturannya),
        // Anda bisa memilih apakah default-nya ON (true) atau OFF (false). 
        // Jika ingin ketat sesuai toggle, jika null anggap tidak punya izin atau cek array-nya.
        if (is_null($permissions)) {
            // Ubah ke true jika ingin default user baru bisa akses semua, atau false jika harus diatur admin dulu
            $hasAccess = true;
        } else {
            // Jika sudah diatur, pastikan $pageKey benar-benar ada di dalam array permissions
            $hasAccess = is_array($permissions) && in_array($pageKey, $permissions);
        }

        // 3. Jika diizinkan, teruskan request
        if ($hasAccess) {
            return $next($request);
        }

        // 4. Jika toggle OFF atau tidak ada izin, tolak dan redirect kembali ke dashboard dengan pesan error
        return redirect()->route('dashboard')
            ->with('error', 'Akses ditolak! Halaman ini telah dinonaktifkan oleh Administrator.');
    }
}
