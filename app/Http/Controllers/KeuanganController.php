<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Pembelian;
use App\Models\Invoice;
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

        // =========================================================================
        // AMBIL DATA TRANSAKSI SELESAI / TERJUAL (Sesuai Logika Histori Rekap)
        // =========================================================================

        // 1. Ambil pembelian berstatus 'Selesai'
        $pembelians = Pembelian::where('status', 'Selesai')->get();

        // 2. Tarik Invoice untuk melengkapi data harga_jual & profit
        $invoices = Invoice::all();

        $jumlahUnit = 0;
        $totalProfitNominal = 0;

        foreach ($pembelians as $item) {
            $itemProfit = 0;
            $isMatchDate = false;

            // Cek apakah ada record di invoice snapshot
            foreach ($invoices as $inv) {
                if (!empty($inv->pembelian_data) && is_array($inv->pembelian_data)) {
                    foreach ($inv->pembelian_data as $snap) {
                        if (
                            (isset($snap['pembelian_id']) && $snap['pembelian_id'] == $item->id) ||
                            (isset($snap['detail_imei']) && !empty($item->detail_imei) && $snap['detail_imei'] == $item->detail_imei)
                        ) {
                            // Ambil profit dari snapshot invoice
                            $itemProfit = $snap['total_profit'] ?? (($snap['harga_jual'] ?? 0) - $item->total_modal);

                            // Cek jika tanggal penerbitan invoice atau updated_at sesuai tanggal filter
                            $tglInvoice = $inv->tanggal ? Carbon::parse($inv->tanggal)->format('Y-m-d') : null;
                            $tglTerbit  = $item->tanggal_terbit ? Carbon::parse($item->tanggal_terbit)->format('Y-m-d') : null;
                            $tglUpdated = Carbon::parse($item->updated_at)->format('Y-m-d');

                            if ($tglInvoice === $tanggal || $tglTerbit === $tanggal || $tglUpdated === $tanggal) {
                                $isMatchDate = true;
                            }
                            break 2;
                        }
                    }
                }
            }

            // Fallback jika tidak ditemukan di invoice: gunakan updated_at/tanggal_terbit
            if (!$isMatchDate) {
                $tglTerbit  = $item->tanggal_terbit ? Carbon::parse($item->tanggal_terbit)->format('Y-m-d') : null;
                $tglUpdated = Carbon::parse($item->updated_at)->format('Y-m-d');

                if ($tglTerbit === $tanggal || $tglUpdated === $tanggal) {
                    $isMatchDate = true;
                    $itemProfit = ($item->harga_jual ?? 0) - $item->total_modal;
                }
            }

            // Akumulasi unit & profit jika cocok dengan tanggal yang dipilih
            if ($isMatchDate) {
                $jumlahUnit++;
                $totalProfitNominal += max(0, $itemProfit);
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

        // Hitung ulang profit tanggal ini
        $pembelians = Pembelian::where('status', 'Selesai')->get();
        $invoices = Invoice::all();
        $totalProfitNominal = 0;

        foreach ($pembelians as $item) {
            $itemProfit = 0;
            $isMatchDate = false;

            foreach ($invoices as $inv) {
                if (!empty($inv->pembelian_data) && is_array($inv->pembelian_data)) {
                    foreach ($inv->pembelian_data as $snap) {
                        if (
                            (isset($snap['pembelian_id']) && $snap['pembelian_id'] == $item->id) ||
                            (isset($snap['detail_imei']) && !empty($item->detail_imei) && $snap['detail_imei'] == $item->detail_imei)
                        ) {
                            $itemProfit = $snap['total_profit'] ?? (($snap['harga_jual'] ?? 0) - $item->total_modal);
                            $tglInvoice = $inv->tanggal ? Carbon::parse($inv->tanggal)->format('Y-m-d') : null;
                            $tglTerbit  = $item->tanggal_terbit ? Carbon::parse($item->tanggal_terbit)->format('Y-m-d') : null;
                            $tglUpdated = Carbon::parse($item->updated_at)->format('Y-m-d');

                            if ($tglInvoice === $tanggal || $tglTerbit === $tanggal || $tglUpdated === $tanggal) {
                                $isMatchDate = true;
                            }
                            break 2;
                        }
                    }
                }
            }

            if (!$isMatchDate) {
                $tglTerbit  = $item->tanggal_terbit ? Carbon::parse($item->tanggal_terbit)->format('Y-m-d') : null;
                $tglUpdated = Carbon::parse($item->updated_at)->format('Y-m-d');

                if ($tglTerbit === $tanggal || $tglUpdated === $tanggal) {
                    $isMatchDate = true;
                    $itemProfit = ($item->harga_jual ?? 0) - $item->total_modal;
                }
            }

            if ($isMatchDate) {
                $totalProfitNominal += max(0, $itemProfit);
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
