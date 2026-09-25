<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Invoice;
use App\Models\Keuangan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        // 1. Tangkap parameter filter tanggal dari masing-masing form
        $startDatePembelian = $request->input('start_date_pembelian');
        $endDatePembelian   = $request->input('end_date_pembelian');

        $startDateNominal   = $request->input('start_date_nominal');
        $endDateNominal     = $request->input('end_date_nominal');

        $startDatePenjualan = $request->input('start_date_penjualan');
        $endDatePenjualan   = $request->input('end_date_penjualan');

        $startDateProfit    = $request->input('start_date_profit');
        $endDateProfit      = $request->input('end_date_profit');

        // ==========================================================
        // A. TOTAL QTY PEMBELIAN (Sesuai Histori Rekap / Status Selesai)
        // ==========================================================
        if (!empty($startDatePembelian) && !empty($endDatePembelian)) {
            $queryQtyPembelian = Pembelian::whereIn('status', ['Sudah Diambil', 'Selesai'])
                ->whereBetween('tanggal_beli', [$startDatePembelian, $endDatePembelian]);
            $totalQtyPembelian = $queryQtyPembelian->count();
        } else {
            $totalQtyPembelian = 0; // Kembali ke 0 jika filter belum diisi / di-reset
        }

        // ==========================================================
        // B. TOTAL NOMINAL PEMBELIAN (Modal)
        // ==========================================================
        if (!empty($startDateNominal) && !empty($endDateNominal)) {
            $queryNominalPembelian = Pembelian::whereBetween('tanggal_beli', [$startDateNominal, $endDateNominal]);
            $totalNominalPembelian = $queryNominalPembelian->sum('total_modal');
        } else {
            $totalNominalPembelian = 0; // Kembali ke 0 jika filter belum diisi / di-reset
        }

        // ==========================================================
        // C. TOTAL PENJUALAN (Dari Tagihan Histori Penjualan / Invoices)
        // ==========================================================
        if (!empty($startDatePenjualan) && !empty($endDatePenjualan)) {
            $queryPenjualan = Invoice::whereBetween('tanggal', [$startDatePenjualan, $endDatePenjualan]);
            $totalPenjualan = $queryPenjualan->sum('total');
        } else {
            $totalPenjualan = 0; // Kembali ke 0 jika filter belum diisi / di-reset
        }

        // ==========================================================
        // D. TOTAL PROFIT
        // ==========================================================
        $totalProfit = 0;
        if (!empty($startDateProfit) && !empty($endDateProfit)) {
            $queryInvoiceProfit = Invoice::whereBetween('tanggal', [$startDateProfit, $endDateProfit]);
            $allInvoices = $queryInvoiceProfit->get();

            foreach ($allInvoices as $inv) {
                if (!empty($inv->pembelian_data) && is_array($inv->pembelian_data)) {
                    foreach ($inv->pembelian_data as $snap) {
                        $modal = (float)($snap['total_modal'] ?? 0);
                        $jual  = (float)($snap['harga_jual'] ?? 0);
                        $totalProfit += ($jual - $modal);
                    }
                }
            }
        } else {
            $totalProfit = 0; // Kembali ke 0 jika filter belum diisi / di-reset
        }

        // ==========================================================
        // E. DATA DASHBOARD KEUANGAN (Total Bersih Aset & Tanggal Input)
        // ==========================================================
        $tanggalHariIni = Carbon::now()->format('Y-m-d');

        // Ambil data keuangan terbaru atau berdasarkan hari ini
        $dataKeuangan = Keuangan::where('tanggal_input', $tanggalHariIni)->first();

        // Jika data hari ini belum ada, ambil data keuangan terakhir yang pernah diinput
        if (!$dataKeuangan) {
            $dataKeuangan = Keuangan::latest('tanggal_input')->first();
        }

        $tanggalInput      = $dataKeuangan ? $dataKeuangan->tanggal_input : $tanggalHariIni;
        $totalBersihAset   = $dataKeuangan ? $dataKeuangan->total_bersih_aset : 0;

        return view('rekap.index', compact(
            'totalQtyPembelian',
            'totalNominalPembelian',
            'totalPenjualan',
            'totalProfit',
            'startDatePembelian',
            'endDatePembelian',
            'startDateNominal',
            'endDateNominal',
            'startDatePenjualan',
            'endDatePenjualan',
            'startDateProfit',
            'endDateProfit',
            'tanggalInput',
            'totalBersihAset'
        ));
    }
}
