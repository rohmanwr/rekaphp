<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Invoice;
use App\Models\Keuangan;
use App\Models\ReturBarang;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->toDateString());

        if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return back()->withErrors(['end_date' => 'Tanggal akhir tidak boleh sebelum tanggal awal.'])->withInput();
        }

        $dateRange = static fn(string $metric): array => [
            $request->input("start_date_{$metric}", $startDate),
            $request->input("end_date_{$metric}", $endDate),
        ];

        [$startDatePembelian, $endDatePembelian] = $dateRange('pembelian');
        [$startDateNominal, $endDateNominal] = $dateRange('nominal');
        [$startDatePenjualan, $endDatePenjualan] = $dateRange('penjualan');
        [$startDateProfit, $endDateProfit] = $dateRange('profit');

        $totalQtyPembelian = Pembelian::whereIn('status', ['Sudah Diambil', 'Selesai'])
            ->whereBetween('tanggal_beli', [$startDatePembelian, $endDatePembelian])
            ->count();

        $totalNominalPembelian = Pembelian::whereBetween('tanggal_beli', [$startDateNominal, $endDateNominal])
            ->sum('total_modal');

        $invoicesPenjualan = Invoice::withSum('returns as retur_total_nilai', 'nilai_retur')
            ->whereBetween('tanggal', [$startDatePenjualan, $endDatePenjualan])
            ->get();
        $totalPenjualan = $invoicesPenjualan->sum(fn($invoice) => max(
            0,
            (float) $invoice->total - (float) ($invoice->retur_total_nilai ?? 0)
        ));
        $totalRetur = $invoicesPenjualan->sum('retur_total_nilai');
        $totalTagihanBelumLunas = $invoicesPenjualan
            ->where('status_payment', 'belum')
            ->sum(fn($invoice) => max(0, (float) $invoice->total - (float) ($invoice->retur_total_nilai ?? 0)));
        $jumlahTagihanBelumLunas = $invoicesPenjualan->where('status_payment', 'belum')->count();

        $invoicesProfit = Invoice::withSum('returns as retur_total_nilai', 'nilai_retur')
            ->withSum('returns as retur_total_modal', 'nilai_modal')
            ->whereBetween('tanggal', [$startDateProfit, $endDateProfit])
            ->get();
        $totalProfit = $invoicesProfit->sum(function ($invoice) {
            $profitSebelumRetur = collect($invoice->pembelian_data ?? [])->sum(
                fn($item) =>
                (float) ($item['harga_jual'] ?? 0) - (float) ($item['total_modal'] ?? 0)
            );
            $profitRetur = (float) ($invoice->retur_total_nilai ?? 0) - (float) ($invoice->retur_total_modal ?? 0);

            return $profitSebelumRetur - $profitRetur;
        });

        $recentInvoices = Invoice::withSum('returns as retur_total_nilai', 'nilai_retur')
            ->whereBetween('tanggal', [$startDatePenjualan, $endDatePenjualan])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $trendStart = Carbon::now()->startOfMonth()->subMonths(5);
        $trendEnd = Carbon::now()->endOfMonth();
        $trendInvoices = Invoice::withSum('returns as retur_total_nilai', 'nilai_retur')
            ->withSum('returns as retur_total_modal', 'nilai_modal')
            ->whereBetween('tanggal', [$trendStart->toDateString(), $trendEnd->toDateString()])
            ->get();
        $trendMonths = collect(range(0, 5))->map(fn($offset) => $trendStart->copy()->addMonths($offset));
        $monthlyTotals = $trendMonths->mapWithKeys(fn($month) => [$month->format('Y-m') => ['sales' => 0, 'profit' => 0]])->all();

        foreach ($trendInvoices as $invoice) {
            $monthKey = Carbon::parse($invoice->tanggal)->format('Y-m');
            $profitSebelumRetur = collect($invoice->pembelian_data ?? [])->sum(
                fn($item) =>
                (float) ($item['harga_jual'] ?? 0) - (float) ($item['total_modal'] ?? 0)
            );
            $monthlyTotals[$monthKey]['sales'] += max(0, (float) $invoice->total - (float) ($invoice->retur_total_nilai ?? 0));
            $monthlyTotals[$monthKey]['profit'] += $profitSebelumRetur
                - (float) ($invoice->retur_total_nilai ?? 0)
                + (float) ($invoice->retur_total_modal ?? 0);
        }

        $trendData = [
            'labels' => $trendMonths->map(fn($month) => $month->format('M Y'))->values(),
            'sales' => collect($monthlyTotals)->pluck('sales')->values(),
            'profit' => collect($monthlyTotals)->pluck('profit')->values(),
        ];

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
            'totalRetur',
            'totalTagihanBelumLunas',
            'jumlahTagihanBelumLunas',
            'recentInvoices',
            'trendData',
            'startDate',
            'endDate',
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
