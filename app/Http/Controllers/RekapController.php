<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Invoice;
use Illuminate\Http\Request;

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
        // Sesuaikan status dalam whereIn dengan status yang digunakan pada Histori Rekap Anda 
        // (misal: 'Sudah Diambil', 'Selesai', atau status lainnya)
        // ==========================================================
        $queryQtyPembelian = Pembelian::whereIn('status', ['Sudah Diambil', 'Selesai']);
        if (!empty($startDatePembelian) && !empty($endDatePembelian)) {
            $queryQtyPembelian->whereBetween('tanggal_beli', [$startDatePembelian, $endDatePembelian]);
        }
        $totalQtyPembelian = $queryQtyPembelian->count();

        // ==========================================================
        // B. TOTAL NOMINAL PEMBELIAN (Modal)
        // ==========================================================
        $queryNominalPembelian = Pembelian::query();
        if (!empty($startDateNominal) && !empty($endDateNominal)) {
            $queryNominalPembelian->whereBetween('tanggal_beli', [$startDateNominal, $endDateNominal]);
        }
        $totalNominalPembelian = $queryNominalPembelian->sum('total_modal');

        // ==========================================================
        // C. TOTAL PENJUALAN (Dari Tagihan Histori Penjualan / Invoices)
        // ==========================================================
        $queryPenjualan = Invoice::query();
        if (!empty($startDatePenjualan) && !empty($endDatePenjualan)) {
            $queryPenjualan->whereBetween('tanggal', [$startDatePenjualan, $endDatePenjualan]);
        }
        $totalPenjualan = $queryPenjualan->sum('total');

        // ==========================================================
        // D. TOTAL PROFIT
        // ==========================================================
        $queryInvoiceProfit = Invoice::query();
        if (!empty($startDateProfit) && !empty($endDateProfit)) {
            $queryInvoiceProfit->whereBetween('tanggal', [$startDateProfit, $endDateProfit]);
        }

        $totalProfit = 0;
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
            'endDateProfit'
        ));
    }
}
