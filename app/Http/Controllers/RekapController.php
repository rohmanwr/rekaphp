<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Pembelian;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $bulanIni = date('m');
        $tahunIni = date('Y');

        // 1. Total Qty/Jumlah Pembelian keseluruhan (atau bulan ini, sesuaikan kebutuhan)
        $totalJumlahPembelian = Pembelian::count();

        // 2. Total Nominal Pembelian Bulan Ini
        $totalNominalPembelianBulanIni = Pembelian::whereMonth('tanggal_beli', $bulanIni)
            ->whereYear('tanggal_beli', $tahunIni)
            ->sum('total_modal');

        // 3. Total Nominal Penjualan Bulan Ini (menggunakan kolom 'harga_jual')
        $totalNominalPenjualanBulanIni = Penjualan::whereMonth('tanggal_jual', $bulanIni)
            ->whereYear('tanggal_jual', $tahunIni)
            ->sum('harga_jual');

        // 4. Total Profit Bersih Bulan Ini (Penjualan terkait dikurang Modal Pembelian)
        $penjualanBulanIni = Penjualan::with('pembelian')
            ->whereMonth('tanggal_jual', $bulanIni)
            ->whereYear('tanggal_jual', $tahunIni)
            ->get();

        $totalProfitBersihBulanIni = $penjualanBulanIni->sum(function ($item) {
            $modal = $item->pembelian->total_modal ?? 0;
            return $item->harga_jual - $modal;
        });

        return view('rekap.index', compact(
            'totalJumlahPembelian',
            'totalNominalPembelianBulanIni',
            'totalNominalPenjualanBulanIni',
            'totalProfitBersihBulanIni'
        ));
    }
}
