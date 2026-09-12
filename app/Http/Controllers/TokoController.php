<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tokos = Toko::when($search, function ($query, $search) {
            return $query->where('kode_toko', 'like', "%{$search}%")
                ->orWhere('nama_toko', 'like', "%{$search}%")
                ->orWhere('lokasi_toko', 'like', "%{$search}%");
        })->latest()->paginate(10);

        return view('toko.index', compact('tokos', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_toko'   => 'required|string|max:50|unique:tokos,kode_toko',
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url',
        ]);

        Toko::create([
            'kode_toko'   => $request->kode_toko,
            'nama_toko'   => $request->nama_toko,
            'lokasi_toko' => $request->lokasi_toko,
            'link_toko'   => $request->link_toko,
        ]);

        return redirect()->route('toko.index')->with('success', 'Data toko berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $toko = Toko::findOrFail($id);

        $request->validate([
            'kode_toko'   => 'required|string|max:50|unique:tokos,kode_toko,' . $toko->id,
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url',
        ]);

        $toko->update([
            'kode_toko'   => $request->kode_toko,
            'nama_toko'   => $request->nama_toko,
            'lokasi_toko' => $request->lokasi_toko,
            'link_toko'   => $request->link_toko,
        ]);

        return redirect()->route('toko.index')->with('success', 'Data toko berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $toko = Toko::findOrFail($id);
        $toko->delete();

        return redirect()->route('toko.index')->with('success', 'Data toko berhasil dihapus!');
    }
}
