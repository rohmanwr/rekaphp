<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $tokos = $query->orderBy('nama_toko')->get();

        return view('toko.index', compact('tokos', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $toko = Toko::create([
                'kode_toko'   => 'TEMP-' . Str::uuid(),
                'nama_toko'   => $request->nama_toko,
                'lokasi_toko' => $request->lokasi_toko,
                'link_toko'   => $request->link_toko,
            ]);

            $nomor = $toko->id;
            do {
                $kodeToko = 'TK-' . str_pad((string) $nomor, 5, '0', STR_PAD_LEFT);
                $nomor++;
            } while (Toko::where('kode_toko', $kodeToko)->where('id', '!=', $toko->id)->exists());

            $toko->update(['kode_toko' => $kodeToko]);
        });

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $toko = Toko::findOrFail($id);

        $request->validate([
            'nama_toko'   => 'required|string|max:255',
            'lokasi_toko' => 'nullable|string|max:255',
            'link_toko'   => 'nullable|url|max:255',
        ]);

        $toko->update($request->only(['nama_toko', 'lokasi_toko', 'link_toko']));

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $toko = Toko::findOrFail($id);
        $toko->delete();

        return redirect()->route('toko.index')->with('success', 'Data Toko berhasil dihapus!');
    }
}
