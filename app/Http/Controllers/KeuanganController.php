<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Invoice;
use App\Models\Barang;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));

        // Ambil data keuangan berdasarkan tanggal yang dipilih
        $keuangan = Keuangan::where('tanggal_input', $tanggal)->first();

        // Ambil SEMUA riwayat data keuangan untuk tabel progress harian
        $riwayatKeuangan = Keuangan::orderBy('tanggal_input', 'desc')->get();

        // Master barang sebagai fallback jika harga jual di snapshot kosong
        $masterBarangs = Barang::all()->keyBy('nama_barang');

        // =========================================================================
        // AMBIL INVOICE HANYA PADA TANGGAL YANG DIPILIH
        // =========================================================================
        $invoices = Invoice::whereDate('tanggal', $tanggal)->get();

        $jumlahUnit = 0;
        $totalProfitNominal = 0;

        foreach ($invoices as $inv) {
            $rawPembelianData = $inv->pembelian_data;
            if (is_string($rawPembelianData)) {
                $rawPembelianData = json_decode($rawPembelianData, true);
            }

            $rawItems = $inv->items;
            if (is_string($rawItems)) {
                $rawItems = json_decode($rawItems, true);
            }

            $sourceItems = !empty($rawPembelianData) && is_array($rawPembelianData)
                ? $rawPembelianData
                : (is_array($rawItems) ? $rawItems : []);

            foreach ($sourceItems as $it) {
                $namaBarangIt = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang');

                // 1. Dapatkan harga jual per item dari snapshot invoice
                $hargaJualItem = 0;
                if (isset($it['harga_jual']) && is_numeric($it['harga_jual']) && $it['harga_jual'] > 0) {
                    $hargaJualItem = (float) $it['harga_jual'];
                } elseif (isset($it['harga']) && is_numeric($it['harga']) && $it['harga'] > 0) {
                    $hargaJualItem = (float) $it['harga'];
                } elseif (isset($it['jumlah']) && isset($it['kuantitas']) && $it['kuantitas'] > 0) {
                    $hargaJualItem = (float) ($it['jumlah'] / $it['kuantitas']);
                } elseif (isset($it['jumlah']) && is_numeric($it['jumlah']) && $it['jumlah'] > 0) {
                    $hargaJualItem = (float) $it['jumlah'];
                } else {
                    $masterBrg = $masterBarangs[$namaBarangIt] ?? null;
                    $hargaJualItem = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
                }

                // 2. Dapatkan modal per item
                $modalItem = isset($it['total_modal']) ? (float) $it['total_modal'] : 0;

                // 3. Ekstrak IMEI jika penjualan berisi banyak unit
                $imeis = [];
                if (isset($it['imei_list']) && is_array($it['imei_list']) && count($it['imei_list']) > 0) {
                    $imeis = $it['imei_list'];
                } else {
                    $rawImei = $it['detail_imei'] ?? ($it['deskripsi_imei'] ?? ($it['imei'] ?? ''));
                    if (!empty($rawImei) && $rawImei !== '-') {
                        $cleaned = str_replace(["\r", ","], "\n", $rawImei);
                        $lines = explode("\n", $cleaned);
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (!empty($trimmed)) {
                                $imeis[] = $trimmed;
                            }
                        }
                    }
                }

                // 4. Hitung Unit & Profit
                if (count($imeis) > 1) {
                    $hargaSatuanUnit = count($imeis) > 0 ? ($hargaJualItem / count($imeis)) : $hargaJualItem;
                    foreach ($imeis as $singleImei) {
                        $totalProfitNominal += ($hargaSatuanUnit - $modalItem);
                        $jumlahUnit++;
                    }
                } else {
                    $profitItem = isset($it['total_profit']) ? (float) $it['total_profit'] : ($hargaJualItem - $modalItem);
                    $totalProfitNominal += $profitItem;
                    $jumlahUnit++;
                }
            }
        }

        // Daftar Bank Dropdown
        $daftarBank = [
            'Bank Jago',
            'Bank BCA',
            'Bank Mandiri',
            'Bank BRI',
            'Bank BNI',
            'BSI',
            'CIMB Niaga',
            'Blu by BCA',
            'SeaBank',
            'GoPay / DANA / OVO'
        ];

        return view('keuangan.index', compact('keuangan', 'tanggal', 'jumlahUnit', 'totalProfitNominal', 'daftarBank', 'riwayatKeuangan'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'tanggal_input' => 'required|date',
            'bank_nama'     => 'required|array',
            'bank_nominal'  => 'required|array',
            'hutang_nama'   => 'nullable|array',
            'hutang_nominal' => 'nullable|array',
        ]);

        $tanggal = $request->tanggal_input;

        $tempatAset = [];
        $totalTempatAset = 0;
        if ($request->has('bank_nama')) {
            foreach ($request->bank_nama as $index => $namaBank) {
                if (!empty($namaBank)) {
                    $nominal = str_replace('.', '', $request->bank_nominal[$index] ?? 0);
                    $tempatAset[$namaBank] = (float) $nominal;
                    $totalTempatAset += (float) $nominal;
                }
            }
        }

        $hutang = [];
        $totalHutang = 0;
        if ($request->has('hutang_nama')) {
            foreach ($request->hutang_nama as $index => $namaPemberi) {
                if (!empty($namaPemberi)) {
                    $nominalHutang = str_replace('.', '', $request->hutang_nominal[$index] ?? 0);
                    $hutang[$namaPemberi] = (float) $nominalHutang;
                    $totalHutang += (float) $nominalHutang;
                }
            }
        }

        // Hitung ulang total profit KHUSUS pada tanggal input dari Invoice Penjualan
        $masterBarangs = Barang::all()->keyBy('nama_barang');
        $invoices = Invoice::whereDate('tanggal', $tanggal)->get();
        $totalProfitNominal = 0;

        foreach ($invoices as $inv) {
            $rawPembelianData = $inv->pembelian_data;
            if (is_string($rawPembelianData)) {
                $rawPembelianData = json_decode($rawPembelianData, true);
            }

            $rawItems = $inv->items;
            if (is_string($rawItems)) {
                $rawItems = json_decode($rawItems, true);
            }

            $sourceItems = !empty($rawPembelianData) && is_array($rawPembelianData)
                ? $rawPembelianData
                : (is_array($rawItems) ? $rawItems : []);

            foreach ($sourceItems as $it) {
                $namaBarangIt = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang');

                $hargaJualItem = 0;
                if (isset($it['harga_jual']) && is_numeric($it['harga_jual']) && $it['harga_jual'] > 0) {
                    $hargaJualItem = (float) $it['harga_jual'];
                } elseif (isset($it['harga']) && is_numeric($it['harga']) && $it['harga'] > 0) {
                    $hargaJualItem = (float) $it['harga'];
                } elseif (isset($it['jumlah']) && isset($it['kuantitas']) && $it['kuantitas'] > 0) {
                    $hargaJualItem = (float) ($it['jumlah'] / $it['kuantitas']);
                } elseif (isset($it['jumlah']) && is_numeric($it['jumlah']) && $it['jumlah'] > 0) {
                    $hargaJualItem = (float) $it['jumlah'];
                } else {
                    $masterBrg = $masterBarangs[$namaBarangIt] ?? null;
                    $hargaJualItem = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
                }

                $modalItem = isset($it['total_modal']) ? (float) $it['total_modal'] : 0;

                $imeis = [];
                if (isset($it['imei_list']) && is_array($it['imei_list']) && count($it['imei_list']) > 0) {
                    $imeis = $it['imei_list'];
                } else {
                    $rawImei = $it['detail_imei'] ?? ($it['deskripsi_imei'] ?? ($it['imei'] ?? ''));
                    if (!empty($rawImei) && $rawImei !== '-') {
                        $cleaned = str_replace(["\r", ","], "\n", $rawImei);
                        $lines = explode("\n", $cleaned);
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (!empty($trimmed)) {
                                $imeis[] = $trimmed;
                            }
                        }
                    }
                }

                if (count($imeis) > 1) {
                    $hargaSatuanUnit = count($imeis) > 0 ? ($hargaJualItem / count($imeis)) : $hargaJualItem;
                    foreach ($imeis as $singleImei) {
                        $totalProfitNominal += ($hargaSatuanUnit - $modalItem);
                    }
                } else {
                    $profitItem = isset($it['total_profit']) ? (float) $it['total_profit'] : ($hargaJualItem - $modalItem);
                    $totalProfitNominal += $profitItem;
                }
            }
        }

        $totalBersihAset = $totalProfitNominal + $totalTempatAset - $totalHutang;

        Keuangan::updateOrCreate(
            ['tanggal_input' => $tanggal],
            [
                'tempat_aset'       => $tempatAset,
                'hutang'            => $hutang,
                'total_bersih_aset' => $totalBersihAset,
            ]
        );

        return redirect()->route('keuangan.index', ['tanggal' => $tanggal])
            ->with('success', 'Progress keuangan tanggal ' . Carbon::parse($tanggal)->translatedFormat('d F Y') . ' berhasil disimpan!');
    }
}
