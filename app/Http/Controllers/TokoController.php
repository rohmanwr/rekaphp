<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Toko::query();

        if (!empty($search)) {
            $query->where('nama_toko', 'like', "%{$search}%")
                ->orWhere('kode_toko', 'like', "%{$search}%")
                ->orWhere('lokasi_toko', 'like', "%{$search}%");
        }

        // Mengambil seluruh data tanpa batasan (menghilangkan paginate/limit)
        $tokos = $query->latest()->get();

        return view('toko.index', compact('tokos', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_toko'   => 'required|string|max:255|unique:tokos,kode_toko',
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url|max:255',
        ]);

        Toko::create($request->all());

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $toko = Toko::findOrFail($id);

        $request->validate([
            'kode_toko'   => 'required|string|max:255|unique:tokos,kode_toko,' . $id,
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url|max:255',
        ]);

        $toko->update($request->all());

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $toko = Toko::findOrFail($id);
        $toko->delete();

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil dihapus!');
    }
}
