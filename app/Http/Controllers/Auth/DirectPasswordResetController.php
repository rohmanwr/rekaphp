<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DirectPasswordResetController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:1'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 1 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tersebut tidak terdaftar di dalam database.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Menggunakan session 'success' agar tertangkap oleh alert sukses
        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}
