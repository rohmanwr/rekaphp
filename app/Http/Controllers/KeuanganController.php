<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));

        // Ambil data keuangan berdasarkan tanggal yang dipilih
        $keuangan = Keuangan::where('tanggal_input', $tanggal)->first();

        // Ambil SEMUA riwayat data keuangan untuk ditampilkan sebagai tabel progress harian (diurutkan dari terbaru)
        $riwayatKeuangan = Keuangan::orderBy('tanggal_input', 'desc')->get();

        // Kalkulasi Total Profit dari Histori Rekap (Berdasarkan tanggal)
        $totalProfitData = Pembelian::whereDate('updated_at', $tanggal)
            ->whereIn('status', ['Jual', 'Selesai', 'Sudah Ready'])
            ->get();

        $jumlahUnit = $totalProfitData->count();

        // Kalkulasi profit dengan pengecekan properti dinamis aman
        $totalProfitNominal = $totalProfitData->sum(function ($item) {
            if (isset($item->laba)) return $item->laba;
            if (isset($item->profit)) return $item->profit;
            if (isset($item->untung)) return $item->untung;

            // Kalkulasi otomatis dari selisih harga jika kolom spesifik ada
            $hargaJual = $item->harga_jual ?? $item->nominal_jual ?? $item->harga_jual_unit ?? 0;
            $hargaBeli = $item->harga_beli ?? $item->total_modal ?? $item->modal ?? 0;

            return max(0, $hargaJual - $hargaBeli);
        });

        // Daftar Bank Pilihan Dropdown
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

        // Kalkulasi profit secara aman tanpa tergantung pada nama kolom 'harga_jual' di SQL Query
        $totalProfitData = Pembelian::whereDate('updated_at', $tanggal)
            ->whereIn('status', ['Jual', 'Selesai', 'Sudah Ready'])
            ->get();

        $totalProfitNominal = $totalProfitData->sum(function ($item) {
            if (isset($item->laba)) return $item->laba;
            if (isset($item->profit)) return $item->profit;
            if (isset($item->untung)) return $item->untung;

            $hargaJual = $item->harga_jual ?? $item->nominal_jual ?? $item->harga_jual_unit ?? 0;
            $hargaBeli = $item->harga_beli ?? $item->total_modal ?? $item->modal ?? 0;

            return max(0, $hargaJual - $hargaBeli);
        });

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
            ->with('success', 'Progress keuangan tanggal ' . Carbon::parse($tanggal)->translatedFormat('d F Y') . ' berhasil disimpan dan diperbarui!');
    }
}
