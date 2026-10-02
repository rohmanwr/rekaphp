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
            'username' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:3'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 3 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Username tersebut tidak terdaftar di dalam database.'])->withInput();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Menggunakan session 'success' agar tertangkap oleh alert sukses
        return redirect()->route('login')->with('success', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}
