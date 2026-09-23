<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name', 'asc')->get();
        return view('user.index', compact('users'));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Hak akses user ' . $user->name . ' berhasil diperbarui!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:5',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Ambil data permissions dari checkbox toggle (jika tidak ada yang dicentang, bernilai array kosong)
        $user->permissions = $request->input('permissions', []);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Hak akses halaman dan data user ' . $user->name . ' berhasil diperbarui!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        // Ubah status bolak-balik antara active dan inactive
        $user->status = (strtolower($user->status ?? 'active') === 'active') ? 'inactive' : 'active';
        $user->save();

        $statusText = $user->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', 'User ' . $user->name . ' berhasil ' . $statusText . '!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $userName = $user->name;
        $user->delete();

        return redirect()->back()->with('success', 'User ' . $userName . ' berhasil dihapus dari sistem!');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Ubah password menjadi 12345 secara otomatis
        $user->password = Hash::make('12345');
        $user->save();

        return redirect()->back()->with('success', 'Password user ' . $user->name . ' berhasil direset menjadi: 12345');
    }
}
