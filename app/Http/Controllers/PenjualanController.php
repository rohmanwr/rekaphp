<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenjualanController extends Controller
{
    /**
     * Menampilkan menu Barang Siap Jual (Status: "Sudah Ready")
     */
    public function siapJual(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $siapJualBarangs = Pembelian::where('status', 'Sudah Ready')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_device', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('penjualan.siap_jual', compact('siapJualBarangs', 'search', 'barangs', 'tokos', 'devices'));
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
                        ->orWhere('imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

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
            'imei'            => 'nullable|string|max:50',
            'status'          => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah',
            'file_lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $data = $request->except(['file_lampiran', 'delete_files']);

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
}
