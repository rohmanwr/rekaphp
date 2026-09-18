<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenjualanController extends Controller
{
    /**
     * Menampilkan menu Barang Siap Jual (Status: "Jual")
     */
    public function siapJual(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $siapJualBarangs = Pembelian::where('status', 'Jual')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_device', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('detail_imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('penjualan.siap_jual', compact('siapJualBarangs', 'search', 'barangs', 'tokos', 'devices'));
    }

    /**
     * Menampilkan menu Histori Penjualan dengan Perbaikan Data Lama (Fallback Aman)
     */
    public function historiPenjualan(Request $request)
    {
        $search = $request->input('search');

        $invoices = Invoice::when($search, function ($query, $search) {
            return $query->where('referensi', 'like', "%{$search}%")
                ->orWhere('nama_pelanggan', 'like', "%{$search}%");
        })
            ->latest()
            ->get();

        // Normalisasi data lama dan baru agar selalu terbaca sempurna di View Histori
        foreach ($invoices as $invoice) {
            // Jika pembelian_data kosong, coba ambil dari items atau buat struktur default
            if (empty($invoice->pembelian_data) || !is_array($invoice->pembelian_data)) {
                $sourceItems = !empty($invoice->items) && is_array($invoice->items) ? $invoice->items : [];
                $normalizedItems = [];

                foreach ($sourceItems as $it) {
                    // Tangani berbagai variasi key dari data lama
                    $imeiVal = '';
                    if (isset($it['detail_imei']) && !empty($it['detail_imei'])) {
                        $imeiVal = $it['detail_imei'];
                    } elseif (isset($it['imei']) && !empty($it['imei'])) {
                        $imeiVal = $it['imei'];
                    } elseif (isset($it['imei_list']) && is_array($it['imei_list'])) {
                        $imeiVal = implode(', ', $it['imei_list']);
                    } else {
                        $imeiVal = '-';
                    }

                    $normalizedItems[] = [
                        'nama_barang'   => $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang Invoice'),
                        'nama_device'   => $it['nama_device'] ?? null,
                        'nama_toko'     => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                        'via'           => $it['via'] ?? '-',
                        'detail_imei'   => $imeiVal,
                        'tanggal_beli'  => $it['tanggal_beli'] ?? $invoice->tanggal,
                        'total_modal'   => $it['total_modal'] ?? ($it['jumlah'] ?? ($invoice->total ?? 0)),
                        'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                }

                // Jika items juga kosong total, buat minimal 1 baris placeholder dari data invoice utama
                if (empty($normalizedItems)) {
                    $normalizedItems[] = [
                        'nama_barang'   => 'Barang Transaksi (Data Lama)',
                        'nama_device'   => null,
                        'nama_toko'     => '-',
                        'via'           => '-',
                        'detail_imei'   => '-',
                        'tanggal_beli'  => $invoice->tanggal,
                        'total_modal'   => $invoice->total ?? 0,
                        'file_lampiran' => [],
                    ];
                }

                $invoice->pembelian_data = $normalizedItems;
                $invoice->save(); // Simpan permanen agar ke depannya langsung termuat cepat
            }
        }

        return view('penjualan.histori', compact('invoices', 'search'));
    }

    /**
     * Menampilkan menu Detail Barang (Status: "Sudah Diambil")
     */
    public function detailBarang(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $detailBarangs = Pembelian::where('status', 'Sudah Diambil')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_device', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('detail_imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('penjualan.detail_barang', compact('detailBarangs', 'search', 'barangs', 'tokos', 'devices'));
    }

    /**
     * Update data detail barang & lampiran
     */
    public function updateDetailBarang(Request $request, $id)
    {
        $request->validate([
            'nama_barang'     => 'required|string|max:255',
            'nama_device'     => 'nullable|string|max:255',
            'nama_toko'       => 'required|string|max:255',
            'via'             => 'required|string|max:255',
            'tanggal_beli'    => 'required|date',
            'total_modal'     => 'required|numeric',
            'detail_imei'     => 'nullable|string|max:250',
            'status'          => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual',
            'file_lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $data = $request->except(['file_lampiran', 'delete_files']);
        $data['detail_imei'] = $request->input('detail_imei');

        $existingFiles = $pembelian->file_lampiran ?? [];

        if ($request->has('delete_files')) {
            $filesToDelete = $request->input('delete_files');
            $remainingFiles = [];

            foreach ($existingFiles as $index => $filePath) {
                if (in_array($index, $filesToDelete)) {
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                } else {
                    $remainingFiles[] = $filePath;
                }
            }
            $existingFiles = $remainingFiles;
        }

        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $existingFiles[] = $file->store('lampiran_pembelian', 'public');
            }
        }

        $data['file_lampiran'] = array_values($existingFiles);
        $pembelian->update($data);

        return redirect()->back()->with('success', 'Data Detail Barang & IMEI berhasil diperbarui!');
    }

    /**
     * Hapus data detail barang beserta file lampirannya
     */
    public function destroyDetailBarang($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (!empty($pembelian->file_lampiran) && is_array($pembelian->file_lampiran)) {
            foreach ($pembelian->file_lampiran as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        $pembelian->delete();

        return redirect()->back()->with('success', 'Data Detail Barang berhasil dihapus!');
    }

    /**
     * Memperbarui rincian item pada invoice lama/histori secara manual
     */
    public function updateHistoriItem(Request $request, $invoiceId)
    {
        $request->validate([
            'item_index'   => 'required|integer',
            'nama_barang'  => 'required|string|max:255',
            'nama_device'  => 'nullable|string|max:255',
            'nama_toko'    => 'required|string|max:255',
            'via'          => 'required|string|max:255',
            'detail_imei'  => 'nullable|string|max:250',
            'total_modal'  => 'required|numeric',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        $isSnapshot = !empty($invoice->pembelian_data);
        $items = $isSnapshot ? $invoice->pembelian_data : ($invoice->items ?? []);

        $index = $request->input('item_index');

        if (isset($items[$index])) {
            $items[$index]['nama_barang'] = $request->input('nama_barang');
            $items[$index]['nama_device'] = $request->input('nama_device');
            $items[$index]['nama_toko']   = $request->input('nama_toko');
            $items[$index]['via']         = $request->input('via');
            $items[$index]['detail_imei'] = $request->input('detail_imei');
            $items[$index]['total_modal'] = $request->input('total_modal');

            if ($isSnapshot) {
                $invoice->pembelian_data = $items;
            } else {
                $invoice->items = $items;
            }

            $invoice->save();

            return redirect()->back()->with('success', 'Rincian data item histori berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Item rincian tidak ditemukan.');
    }
}
