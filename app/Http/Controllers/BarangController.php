<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Invoice;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::when($search, function ($query, $search) {
            return $query->where('kode_barang', 'like', "%{$search}%")
                ->orWhere('nama_barang', 'like', "%{$search}%");
        })->latest()->get(); // Menggunakan get() agar menampilkan seluruh data tanpa batasan

        return view('barang.index', compact('barangs', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barangs,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'harga_jual'  => 'required',
        ]);

        Barang::create([
            'kode_barang' => $request->kode_barang,
            'nama_barang' => $request->nama_barang,
            'harga_jual'  => str_replace('.', '', $request->harga_jual), // Bersihkan titik format rupiah
        ]);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang' => 'required|string|max:255',
            'harga_jual'  => 'required',
        ]);

        $namaBarangSebelumnya = $barang->nama_barang;
        $namaBarangBaru = $request->nama_barang;
        $hargaJualBaru = (int) str_replace('.', '', $request->harga_jual);
        $jumlahDisinkronkan = 0;
        $jumlahTerkunci = 0;

        DB::transaction(function () use ($barang, $request, $namaBarangSebelumnya, $namaBarangBaru, $hargaJualBaru, &$jumlahDisinkronkan, &$jumlahTerkunci) {
            $barang->update([
                'kode_barang' => $request->kode_barang,
                'nama_barang' => $namaBarangBaru,
                'harga_jual'  => $hargaJualBaru,
            ]);

            if ($namaBarangSebelumnya !== $namaBarangBaru) {
                return;
            }

            $pembelians = Pembelian::where('nama_barang', $namaBarangBaru)
                ->where('titipan', 'Ya, (tidak ambil untung)')
                ->get();
            $pembelianIds = [];

            foreach ($pembelians as $pembelian) {
                if (Invoice::isLockedForPurchase($pembelian)) {
                    $jumlahTerkunci++;
                    continue;
                }

                DB::table('pembelians')->where('id', $pembelian->id)->update([
                    'total_modal' => $hargaJualBaru,
                    'updated_at' => DB::raw('updated_at'),
                ]);
                $pembelianIds[] = $pembelian->id;
            }

            if (empty($pembelianIds)) {
                return;
            }

            foreach (DB::table('invoices')->whereNotNull('pembelian_data')->get(['id', 'pembelian_data']) as $invoice) {
                $snapshotItems = is_array($invoice->pembelian_data)
                    ? $invoice->pembelian_data
                    : json_decode($invoice->pembelian_data ?? '[]', true);
                if (!is_array($snapshotItems)) {
                    continue;
                }

                $changed = false;
                foreach ($snapshotItems as &$snapshotItem) {
                    if (!in_array((int) ($snapshotItem['pembelian_id'] ?? 0), $pembelianIds, true)) {
                        continue;
                    }

                    $snapshotItem['total_modal'] = $hargaJualBaru;
                    $snapshotItem['total_profit'] = (float) ($snapshotItem['harga_jual'] ?? 0) - $hargaJualBaru;
                    $changed = true;
                }
                unset($snapshotItem);

                if ($changed) {
                    DB::table('invoices')->where('id', $invoice->id)->update([
                        'pembelian_data' => json_encode($snapshotItems),
                    ]);
                }
            }

            $jumlahDisinkronkan = count($pembelianIds);
        });

        $message = 'Data barang berhasil diperbarui.';
        if ($jumlahDisinkronkan > 0) {
            $message .= " Total modal {$jumlahDisinkronkan} barang titipan ikut disesuaikan dengan harga master.";
        }
        if ($jumlahTerkunci > 0) {
            $message .= " {$jumlahTerkunci} barang terkunci invoice tidak diubah.";
        }

        return redirect()->route('barang.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus!');
    }
}
