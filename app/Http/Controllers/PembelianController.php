<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedStatus = $request->input('status');

        $query = Pembelian::with('user');

        if (!empty($selectedStatus)) {
            $query->where('status', $selectedStatus);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_manual', 'like', "%{$search}%")
                    ->orWhere('nama_alamat', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('nama_toko', 'like', "%{$search}%")
                    ->orWhere('detail_imei', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $pembelians = $query->orderBy('tanggal_beli', 'desc')->get();
        $barangs    = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos      = Toko::orderBy('nama_toko', 'asc')->get();
        $devices    = Device::orderBy('nama_device', 'asc')->get();

        return view('pembelian.index', compact('pembelians', 'barangs', 'tokos', 'devices', 'search', 'selectedStatus'));
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
            'kode_manual'     => 'required|string|max:255',
            'nama_alamat'     => 'nullable|string|max:255',
            'nama_barang'     => 'required|string|max:255',
            'nama_toko'       => 'required|string|max:255',
            'via'             => 'required|string|max:255',
            'tanggal_beli'    => 'required|date',
            'total_modal'     => 'required',
            'status'          => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual',
            'detail_imei'     => 'nullable|string',
            'file_lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->except('file_lampiran');
        $data['user_id'] = Auth::id();
        $data['total_modal'] = str_replace('.', '', $data['total_modal']);
        $data['kode_otomatis'] = 'TRX-' . date('Ymd') . '-' . rand(100, 999);
        $data['detail_imei'] = $request->input('detail_imei');

        $files = [];
        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $files[] = $file->store('lampiran_pembelian', 'public');
            }
        }
        $data['file_lampiran'] = $files;

        Pembelian::create($data);

        return redirect()->route('pembelian.index')->with('success', 'Data Pembelian berhasil ditambahkan!');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        $pembelian->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status barang berhasil diperbarui!');
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        $request->validate([
            'kode_manual'  => 'required|string|max:255',
            'nama_alamat'  => 'nullable|string|max:255',
            'nama_barang'  => 'required|string|max:255',
            'nama_toko'    => 'required|string|max:255',
            'via'          => 'required|string|max:255',
            'tanggal_beli' => 'required|date',
            'total_modal'  => 'required',
            'status'       => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual',
            'detail_imei'  => 'nullable|string',
        ]);

        $data = $request->except(['file_lampiran', 'delete_files']);
        $data['total_modal'] = str_replace('.', '', $data['total_modal']);
        $data['detail_imei'] = $request->input('detail_imei');

        $currentFiles = $pembelian->file_lampiran ?? [];

        if ($request->has('delete_files')) {
            foreach ($request->delete_files as $delIdx) {
                if (isset($currentFiles[$delIdx])) {
                    Storage::disk('public')->delete($currentFiles[$delIdx]);
                    unset($currentFiles[$delIdx]);
                }
            }
            $currentFiles = array_values($currentFiles);
        }

        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $currentFiles[] = $file->store('lampiran_pembelian', 'public');
            }
        }

        $data['file_lampiran'] = $currentFiles;
        $pembelian->update($data);

        return redirect()->route('pembelian.index')->with('success', 'Data Pembelian berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (!empty($pembelian->file_lampiran)) {
            foreach ($pembelian->file_lampiran as $filePath) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $pembelian->delete();

        return redirect()->route('pembelian.index')->with('success', 'Data Pembelian berhasil dihapus!');
    }

    public function barangSiapJual(Request $request)
    {
        $search = $request->input('search');

        $pembelians = Pembelian::where('status', 'Jual')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $barangs = Barang::all()->keyBy('nama_barang');

        return view('pembelian.siap_jual', compact('pembelians', 'search', 'barangs'));
    }

    public function createInvoice(Request $request)
    {
        $ids = $request->input('pembelian_ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu barang yang ingin dijual.');
        }

        $pembelians = Pembelian::whereIn('id', $ids)->get();
        $barangs = Barang::all()->keyBy('nama_barang');

        $groupedItems = [];
        foreach ($pembelians as $item) {
            $namaBarang = $item->nama_barang;
            $masterBarang = $barangs[$namaBarang] ?? null;
            $hargaJual = $masterBarang ? $masterBarang->harga_jual : 0;

            $imeis = [];
            if (!empty($item->detail_imei)) {
                $cleanedImei = str_replace(["\r", ","], "\n", $item->detail_imei);
                $lines = explode("\n", $cleanedImei);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (!empty($trimmed)) {
                        $imeis[] = $trimmed;
                    }
                }
            }

            if (!isset($groupedItems[$namaBarang])) {
                $groupedItems[$namaBarang] = [
                    'pembelian_ids' => [$item->id],
                    'nama_barang' => $namaBarang,
                    'imei_list' => $imeis,
                    'kuantitas' => count($imeis) > 0 ? count($imeis) : 1,
                    'harga' => $hargaJual,
                ];
                $groupedItems[$namaBarang]['jumlah'] = $groupedItems[$namaBarang]['kuantitas'] * $hargaJual;
            } else {
                $groupedItems[$namaBarang]['pembelian_ids'][] = $item->id;
                $groupedItems[$namaBarang]['imei_list'] = array_merge($groupedItems[$namaBarang]['imei_list'], $imeis);
                $groupedItems[$namaBarang]['kuantitas'] = count($groupedItems[$namaBarang]['imei_list']);
                $groupedItems[$namaBarang]['jumlah'] = $groupedItems[$namaBarang]['kuantitas'] * $hargaJual;
            }
        }

        $lastInvoice = Invoice::latest()->first();
        $nextNumber = $lastInvoice ? ((int)substr($lastInvoice->referensi, -5)) + 1 : 1;
        $referensi = 'INV/' . sprintf('%05d', $nextNumber);

        return view('pembelian.buat_invoice', compact('groupedItems', 'referensi'));
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'referensi'      => 'required|string|unique:invoices,referensi',
            'tanggal'        => 'required|date',
            'jatuh_tempo'    => 'required|date',
            'nama_pelanggan' => 'required|string|max:255',
            'items'          => 'required|array',
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['jumlah'];
        }

        $penyebut = function ($nilai) use (&$penyebut) {
            $nilai = abs((int)$nilai);
            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " " . $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = $penyebut($nilai - 10) . " Belas";
            } else if ($nilai < 100) {
                $temp = $penyebut((int)($nilai / 10)) . " Puluh" . $penyebut($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " Seratus" . $penyebut($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = $penyebut((int)($nilai / 100)) . " Ratus" . $penyebut($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " Seribu" . $penyebut($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = $penyebut((int)($nilai / 1000)) . " Ribu" . $penyebut($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = $penyebut((int)($nilai / 1000000)) . " Juta" . $penyebut($nilai % 1000000);
            } else if ($nilai < 1000000000000) {
                $temp = $penyebut((int)($nilai / 1000000000)) . " Milyar" . $penyebut(fmod($nilai, 1000000000));
            }
            return $temp;
        };

        $terbilangStr = trim($penyebut($subtotal)) . " Rupiah";

        // Kumpulkan ID pembelian yang dipilih
        $allPembelianIds = [];
        foreach ($request->items as $item) {
            if (isset($item['pembelian_ids'])) {
                foreach ($item['pembelian_ids'] as $pId) {
                    $allPembelianIds[] = $pId;
                }
            }
        }

        // Ambil data pembelian asli sebelum dihapus
        $pembelianItems = Pembelian::whereIn('id', $allPembelianIds)->get();

        // Bentuk snapshot array secara eksplisit agar aman terbaca di Histori Penjualan
        $snapshotItems = [];
        foreach ($pembelianItems as $pItem) {
            $snapshotItems[] = [
                'nama_barang'   => $pItem->nama_barang,
                'nama_device'   => $pItem->nama_device ?? null,
                'nama_toko'     => $pItem->nama_toko,
                'via'           => $pItem->via,
                'detail_imei'   => $pItem->detail_imei ?? '-',
                'tanggal_beli'  => $pItem->tanggal_beli,
                'total_modal'   => $pItem->total_modal,
                'file_lampiran' => $pItem->file_lampiran ?? [],
            ];
        }

        $invoice = Invoice::create([
            'referensi'        => $request->referensi,
            'tanggal'          => $request->tanggal,
            'jatuh_tempo'      => $request->jatuh_tempo,
            'nama_pelanggan'   => $request->nama_pelanggan,
            'alamat_pelanggan' => $request->alamat_pelanggan ?? 'DKI Jakarta',
            'items'            => $request->items,
            'pembelian_data'   => $snapshotItems, // Snapshot tersimpan rapi
            'subtotal'         => $subtotal,
            'total'            => $subtotal,
            'terbilang'        => $terbilangStr,
        ]);

        // HAPUS DATA DARI TABEL PEMBELIAN SETELAH INVOICE DITERBITKAN
        if (!empty($allPembelianIds)) {
            foreach ($pembelianItems as $pItem) {
                if (!empty($pItem->file_lampiran)) {
                    foreach ($pItem->file_lampiran as $filePath) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
                $pItem->delete();
            }
        }

        return redirect()->route('invoice.show', $invoice->id)->with('success', 'Invoice berhasil diterbitkan dan data otomatis terhapus dari rekap pembelian!');
    }

    public function showInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        return view('pembelian.print_invoice', compact('invoice'));
    }
}
