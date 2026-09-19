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

        // Gunakan keyBy('nama_barang') agar $barangs[$item->nama_barang] bisa langsung diakses
        $barangs = Barang::orderBy('nama_barang', 'asc')->get()->keyBy('nama_barang');
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

        // Ubah variabel $siapJualBarangs menjadi $pembelians agar cocok dengan Blade view Anda
        $pembelians = $siapJualBarangs;

        return view('penjualan.siap_jual', compact('pembelians', 'search', 'barangs', 'tokos', 'devices'));
    }
    /**
     * Menampilkan menu Histori Penjualan dengan Mutlak Lock Harga Invoice (Tanpa Mengubah Master)
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

        foreach ($invoices as $invoice) {
            $sourceItems = !empty($invoice->pembelian_data) && is_array($invoice->pembelian_data)
                ? $invoice->pembelian_data
                : (!empty($invoice->items) && is_array($invoice->items) ? $invoice->items : []);

            $normalizedItems = [];
            $dataHasChanged = false;

            foreach ($sourceItems as $it) {
                $namaBarang = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang Invoice');

                $imeiVal = '-';
                if (isset($it['detail_imei']) && !empty($it['detail_imei'])) {
                    $imeiVal = $it['detail_imei'];
                } elseif (isset($it['imei']) && !empty($it['imei'])) {
                    $imeiVal = $it['imei'];
                } elseif (isset($it['deskripsi_imei']) && !empty($it['deskripsi_imei'])) {
                    $imeiVal = $it['deskripsi_imei'];
                } elseif (isset($it['imei_list']) && is_array($it['imei_list'])) {
                    $imeiVal = implode("\n", $it['imei_list']);
                }

                $tokoVal = $it['nama_toko'] ?? ($it['toko'] ?? '-');
                $viaVal = $it['via'] ?? ($it['platform'] ?? '-');
                $tglBeliVal = $it['tanggal_beli'] ?? $invoice->tanggal;
                $lampiranVal = $it['file_lampiran'] ?? [];

                // Cari data asli ke tabel Pembelian untuk Total Modal mutlak
                $pembelianRecord = null;
                if (isset($it['pembelian_ids']) && is_array($it['pembelian_ids']) && count($it['pembelian_ids']) > 0) {
                    $pembelianRecord = Pembelian::whereIn('id', $it['pembelian_ids'])->first();
                } else {
                    $pembelianRecord = Pembelian::where('nama_barang', 'like', "%{$namaBarang}%")
                        ->orderBy('tanggal_beli', 'desc')
                        ->first();
                }

                $modalVal = 0;
                if ($pembelianRecord && !empty($pembelianRecord->total_modal)) {
                    $modalVal = $pembelianRecord->total_modal;
                } else {
                    $modalVal = $it['total_modal'] ?? 0;
                }

                if ($pembelianRecord) {
                    if ($imeiVal === '-' || empty($imeiVal)) $imeiVal = $pembelianRecord->detail_imei ?? '-';
                    if ($tokoVal === '-' || empty($tokoVal)) $tokoVal = $pembelianRecord->nama_toko ?? '-';
                    if ($viaVal === '-' || empty($viaVal)) $viaVal = $pembelianRecord->via ?? '-';
                    if (empty($lampiranVal)) $lampiranVal = $pembelianRecord->file_lampiran ?? [];
                    if (empty($tglBeliVal)) $tglBeliVal = $pembelianRecord->tanggal_beli;
                }

                // =========================================================================
                // PENGUNCIAN HARGA JUAL HISTORIS MUTLAK (TIDAK LAGI MEMBACA MASTER BARANG)
                // =========================================================================
                $hargaJualVal = 0;
                if (isset($it['harga_jual']) && is_numeric($it['harga_jual'])) {
                    $hargaJualVal = (float) $it['harga_jual'];
                } elseif (isset($it['harga']) && is_numeric($it['harga'])) {
                    $hargaJualVal = (float) $it['harga'];
                } elseif (isset($it['jumlah']) && isset($it['kuantitas']) && $it['kuantitas'] > 0) {
                    $hargaJualVal = (float) ($it['jumlah'] / $it['kuantitas']);
                } elseif (isset($it['jumlah']) && is_numeric($it['jumlah'])) {
                    $hargaJualVal = (float) $it['jumlah'];
                } else {
                    $hargaJualVal = 0; // Kunci tetap 0 jika memang tidak ada data historis, TANPA mencarinya ke master barang
                }

                // Hitung Total Profit = Harga Jual - Total Modal
                $profitVal = $hargaJualVal - $modalVal;

                if (!isset($it['harga_jual']) || $it['harga_jual'] != $hargaJualVal) {
                    $dataHasChanged = true;
                }

                $normalizedItems[] = [
                    'nama_barang'   => $namaBarang,
                    'nama_device'   => $it['nama_device'] ?? ($pembelianRecord->nama_device ?? null),
                    'nama_toko'     => $tokoVal,
                    'via'           => $viaVal,
                    'detail_imei'   => $imeiVal,
                    'tanggal_beli'  => $tglBeliVal,
                    'total_modal'   => $modalVal,
                    'harga_jual'    => $hargaJualVal, // Terkunci mutlak sesuai data cetak invoice
                    'total_profit'  => $profitVal,
                    'file_lampiran' => $lampiranVal,
                ];
            }

            if ($dataHasChanged && !empty($normalizedItems)) {
                $invoice->pembelian_data = $normalizedItems;
                $invoice->save();
            }
        }

        return view('penjualan.histori', compact('invoices', 'search'));
    }

    public function detailBarang(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $detailBarangs = Pembelian::where('status', 'Selesai')
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
