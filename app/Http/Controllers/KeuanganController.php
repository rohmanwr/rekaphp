<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\Invoice;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\ReturBarang;
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
        // Profit histori penjualan dikelompokkan berdasarkan tanggal pembayaran.
        // =========================================================================
        $profitSummary = $this->profitSummaryForPaymentDate($tanggal, $masterBarangs);
        $jumlahUnit = $profitSummary['jumlahUnit'];
        $totalProfitNominal = $profitSummary['totalProfitNominal'];

        $previousTotalBersihAset = null;
        foreach ($riwayatKeuangan->sortBy('tanggal_input') as $row) {
            $currentTotalBersihAset = (float) $row->total_bersih_aset;
            $row->setAttribute(
                'total_profit_nominal',
                $previousTotalBersihAset === null ? null : $currentTotalBersihAset - $previousTotalBersihAset
            );
            $previousTotalBersihAset = $currentTotalBersihAset;
        }

        // =========================================================================
        // AMBIL TOTAL ASET HANDPHONE (STATUS: "Sudah Diambil")
        // =========================================================================
        $asetHpItems = Pembelian::where('status', 'Sudah Diambil')->get();
        $totalUnitAsetHp = $asetHpItems->count();
        $totalModalAsetHp = (float) $asetHpItems->sum('total_modal');

        // Daftar Bank Dropdown
        $daftarBank = [
            'Bank Jago',
            'Bank BCA',
            'Bank UOB',
            'Bank Mandiri',
            'Bank BNI',
            'BSI',
            'SeaBank',
            'Blu by BCA',
            'GoPay / DANA / OVO'
        ];

        return view('keuangan.index', compact(
            'keuangan',
            'tanggal',
            'jumlahUnit',
            'totalProfitNominal',
            'totalUnitAsetHp',
            'totalModalAsetHp',
            'daftarBank',
            'riwayatKeuangan'
        ));
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

        // =========================================================================
        // CEK APAKAH USER MENGKLIK TOMBOL DELETE ASET HP SEBELUM MENYIMPAN
        // =========================================================================
        $isAsetHpDeleted = $request->input('is_aset_hp_deleted', '0');
        if ($isAsetHpDeleted == '1' || $isAsetHpDeleted === 1) {
            $totalModalAsetHp = 0;
        } else {
            $totalModalAsetHp = (float) Pembelian::where('status', 'Sudah Diambil')->sum('total_modal');
        }

        // Total bersih aset = modal aset HP + bank - hutang
        $totalBersihAset = $totalModalAsetHp + $totalTempatAset - $totalHutang;

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

    private function profitSummaryForPaymentDate(string $tanggal, $masterBarangs): array
    {
        $invoices = Invoice::whereIn('status_payment', ['sudah', 'Sudah'])
            ->whereDate('tanggal_payment', $tanggal)
            ->get();

        $jumlahUnit = 0;
        $totalProfitNominal = 0;

        foreach ($invoices as $invoice) {
            $rawPembelianData = $invoice->pembelian_data;
            if (is_string($rawPembelianData)) {
                $rawPembelianData = json_decode($rawPembelianData, true);
            }

            $rawItems = $invoice->items;
            if (is_string($rawItems)) {
                $rawItems = json_decode($rawItems, true);
            }

            $sourceItems = !empty($rawPembelianData) && is_array($rawPembelianData)
                ? $rawPembelianData
                : (is_array($rawItems) ? $rawItems : []);

            foreach ($sourceItems as $item) {
                $namaBarang = $item['nama_barang'] ?? ($item['deskripsi'] ?? 'Barang');
                $hargaJual = 0;

                if (isset($item['harga_jual']) && is_numeric($item['harga_jual']) && $item['harga_jual'] > 0) {
                    $hargaJual = (float) $item['harga_jual'];
                } elseif (isset($item['harga']) && is_numeric($item['harga']) && $item['harga'] > 0) {
                    $hargaJual = (float) $item['harga'];
                } elseif (isset($item['jumlah'], $item['kuantitas']) && $item['kuantitas'] > 0) {
                    $hargaJual = (float) ($item['jumlah'] / $item['kuantitas']);
                } elseif (isset($item['jumlah']) && is_numeric($item['jumlah']) && $item['jumlah'] > 0) {
                    $hargaJual = (float) $item['jumlah'];
                } else {
                    $masterBarang = $masterBarangs[$namaBarang] ?? null;
                    $hargaJual = (float) ($masterBarang->harga_jual ?? ($masterBarang->harga ?? 0));
                }

                $modalItem = isset($item['total_modal']) ? (float) $item['total_modal'] : 0;
                $imeis = [];

                if (isset($item['imei_list']) && is_array($item['imei_list']) && count($item['imei_list']) > 0) {
                    $imeis = $item['imei_list'];
                } else {
                    $rawImei = $item['detail_imei'] ?? ($item['deskripsi_imei'] ?? ($item['imei'] ?? ''));
                    if (!empty($rawImei) && $rawImei !== '-') {
                        $cleaned = str_replace(["\r", ","], "\n", $rawImei);
                        foreach (explode("\n", $cleaned) as $line) {
                            $trimmed = trim($line);
                            if ($trimmed !== '') {
                                $imeis[] = $trimmed;
                            }
                        }
                    }
                }

                if (count($imeis) > 1) {
                    $hargaSatuanUnit = $hargaJual / count($imeis);
                    $totalProfitNominal += count($imeis) * ($hargaSatuanUnit - $modalItem);
                    $jumlahUnit += count($imeis);
                } else {
                    $totalProfitNominal += isset($item['total_profit'])
                        ? (float) $item['total_profit']
                        : ($hargaJual - $modalItem);
                    $jumlahUnit++;
                }
            }
        }

        $totalProfitNominal -= $this->returnProfitForPaymentDate($tanggal);

        return [
            'jumlahUnit' => $jumlahUnit,
            'totalProfitNominal' => $totalProfitNominal,
        ];
    }

    private function returnProfitForPaymentDate(string $tanggal): float
    {
        return (float) ReturBarang::whereHas('invoice', fn($query) => $query->whereDate('tanggal_payment', $tanggal))
            ->get()
            ->sum(fn($return) => (float) $return->nilai_retur - (float) $return->nilai_modal);
    }
}
