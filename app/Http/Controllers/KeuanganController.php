<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Pembelian;
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

        // Master barang sebagai fallback
        $masterBarangs = Barang::all()->keyBy('nama_barang');

        // Tarik semua data pembelian yang sudah Selesai / Terjual
        $pembelians = Pembelian::where('status', 'Selesai')->get();
        $invoices   = Invoice::all();

        $jumlahUnit = 0;
        $totalProfitNominal = 0;

        foreach ($pembelians as $item) {
            // Cek apakah tanggal terbit / updated_at / tanggal invoice cocok dengan filter
            $tglItem = null;
            if (!empty($item->tanggal_terbit)) {
                $tglItem = Carbon::parse($item->tanggal_terbit)->format('Y-m-d');
            } elseif (!empty($item->updated_at)) {
                $tglItem = Carbon::parse($item->updated_at)->format('Y-m-d');
            }

            // Jika tanggal cocok DENGAN tanggal yang dipilih di form
            if ($tglItem === $tanggal) {
                $hargaJualItem = (float) ($item->harga_jual ?? 0);
                $modalItem     = (float) ($item->total_modal ?? 0);

                // Cari apakah ada snapshot harga jual khusus di invoice
                foreach ($invoices as $inv) {
                    $rawPembelianData = $inv->pembelian_data;
                    if (is_string($rawPembelianData)) {
                        $rawPembelianData = json_decode($rawPembelianData, true);
                    }

                    if (!empty($rawPembelianData) && is_array($rawPembelianData)) {
                        foreach ($rawPembelianData as $snap) {
                            if (
                                (isset($snap['pembelian_id']) && $snap['pembelian_id'] == $item->id) ||
                                (isset($snap['detail_imei']) && !empty($item->detail_imei) && $snap['detail_imei'] == $item->detail_imei)
                            ) {
                                if (isset($snap['harga_jual']) && $snap['harga_jual'] > 0) {
                                    $hargaJualItem = (float) $snap['harga_jual'];
                                }
                                break 2;
                            }
                        }
                    }
                }

                // Jika harga jual masih 0, ambil dari master barang
                if ($hargaJualItem == 0) {
                    $masterBrg = $masterBarangs[$item->nama_barang] ?? null;
                    $hargaJualItem = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
                }

                $profitItem = $hargaJualItem - $modalItem;

                $jumlahUnit++;
                $totalProfitNominal += $profitItem;
            }
        }

        // FALLBACK: Jika pencocokan tanggal harian 0 unit, kalkulasikan SEMUA data invoice yang ada
        if ($jumlahUnit === 0) {
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

                    $hargaJualHistori = 0;
                    if (isset($it['harga_jual']) && is_numeric($it['harga_jual']) && $it['harga_jual'] > 0) {
                        $hargaJualHistori = (float) $it['harga_jual'];
                    } elseif (isset($it['harga']) && is_numeric($it['harga']) && $it['harga'] > 0) {
                        $hargaJualHistori = (float) $it['harga'];
                    } else {
                        $masterBrg = $masterBarangs[$namaBarangIt] ?? null;
                        $hargaJualHistori = (float) ($masterBrg->harga_jual ?? 0);
                    }

                    $modalIt = isset($it['total_modal']) ? (float) $it['total_modal'] : 0;
                    $profitIt = isset($it['total_profit']) ? (float) $it['total_profit'] : ($hargaJualHistori - $modalIt);

                    $totalProfitNominal += $profitIt;
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

        // Hitung ulang total profit
        $masterBarangs = Barang::all()->keyBy('nama_barang');
        $pembelians = Pembelian::where('status', 'Selesai')->get();
        $invoices = Invoice::all();
        $totalProfitNominal = 0;

        foreach ($pembelians as $item) {
            $hargaJualItem = (float) ($item->harga_jual ?? 0);
            $modalItem     = (float) ($item->total_modal ?? 0);

            foreach ($invoices as $inv) {
                $rawPembelianData = $inv->pembelian_data;
                if (is_string($rawPembelianData)) {
                    $rawPembelianData = json_decode($rawPembelianData, true);
                }

                if (!empty($rawPembelianData) && is_array($rawPembelianData)) {
                    foreach ($rawPembelianData as $snap) {
                        if (
                            (isset($snap['pembelian_id']) && $snap['pembelian_id'] == $item->id) ||
                            (isset($snap['detail_imei']) && !empty($item->detail_imei) && $snap['detail_imei'] == $item->detail_imei)
                        ) {
                            if (isset($snap['harga_jual']) && $snap['harga_jual'] > 0) {
                                $hargaJualItem = (float) $snap['harga_jual'];
                            }
                            break 2;
                        }
                    }
                }
            }

            if ($hargaJualItem == 0) {
                $masterBrg = $masterBarangs[$item->nama_barang] ?? null;
                $hargaJualItem = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
            }

            $totalProfitNominal += ($hargaJualItem - $modalItem);
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
