<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedStatus = $request->input('status');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $pembelians = Pembelian::when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('kode_otomatis', 'like', "%{$search}%")
                    ->orWhere('kode_manual', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('nama_device', 'like', "%{$search}%")
                    ->orWhere('nama_toko', 'like', "%{$search}%");
            });
        })->when($selectedStatus, function ($query, $selectedStatus) {
            return $query->where('status', $selectedStatus);
        })->latest()->paginate(10);

        return view('pembelian.index', compact('pembelians', 'search', 'selectedStatus', 'barangs', 'tokos', 'devices'));
    }

    public function create()
    {
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        return view('pembelian.create', compact('barangs', 'tokos', 'devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang'     => 'required|string|max:255',
            'nama_device'     => 'nullable|string|max:255',
            'nama_toko'       => 'required|string|max:255',
            'via'             => 'required|string|max:255',
            'tanggal_beli'    => 'required|date',
            'total_modal'     => 'required|numeric',
            'status'          => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah',
            'file_lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $today = date('Ymd');
        $lastRecord = Pembelian::whereDate('created_at', date('Y-m-d'))->latest()->first();
        $sequence = $lastRecord ? sprintf('%04d', (int)substr($lastRecord->kode_otomatis, -4) + 1) : '0001';
        $kodeOtomatis = "BUY-{$today}-{$sequence}";

        $uploadedFiles = [];
        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $uploadedFiles[] = $file->store('lampiran_pembelian', 'public');
            }
        }

        Pembelian::create([
            'kode_otomatis' => $kodeOtomatis,
            'kode_manual'   => $request->kode_manual,
            'nama_barang'   => $request->nama_barang,
            'nama_device'   => $request->nama_device,
            'nama_toko'     => $request->nama_toko,
            'via'           => $request->via,
            'tanggal_beli'  => $request->tanggal_beli,
            'total_modal'   => $request->total_modal,
            'status'        => $request->status ?? 'Belum Ready',
            'file_lampiran' => $uploadedFiles,
        ]);

        return redirect()->route('pembelian.index')->with('success', 'Rekap Pembelian berhasil disimpan dengan kode: ' . $kodeOtomatis);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $pembelian->status = $request->status;
        $pembelian->save();

        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui menjadi: ' . $request->status);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_barang'     => 'required|string|max:255',
            'nama_device'     => 'nullable|string|max:255',
            'nama_toko'       => 'required|string|max:255',
            'via'             => 'required|string|max:255',
            'tanggal_beli'    => 'required|date',
            'total_modal'     => 'required|numeric',
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

        return redirect()->route('pembelian.index')->with('success', 'Data rekap pembelian berhasil diperbarui!');
    }

    public function destroy($id)
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

        return redirect()->route('pembelian.index')->with('success', 'Data transaksi pembelian berhasil dihapus!');
    }
}
