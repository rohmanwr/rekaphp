@extends('layouts.app')

@section('title', 'Invoice - ' . $invoice->referensi)

@section('content')
<div class="container my-4">
    <!-- Tombol Aksi Print / Kembali (Akan otomatis disembunyikan saat diprint) -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none" style="max-width: 210mm; margin: 0 auto 1.5rem auto;">
        <a href="{{ route('pembelian.siap_jual') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Siap Jual
        </a>
        <button onclick="window.print()" class="btn btn-primary fw-semibold">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Print Invoice
        </button>
    </div>

    <!-- Kertas Invoice Ukuran A4 (21cm x 29.7cm) dengan Posisi di Tengah -->
    <div class="card border-0 shadow-sm p-5 bg-white mx-auto invoice-container">
        <!-- Header Invoice -->
        <div class="row justify-content-between mb-4">
            <div class="col-6">
                <h2 class="fw-bold text-primary mb-1">Rohman Store</h2>
            </div>
            <div class="col-6 text-end">
                <h3 class="fw-bold text-primary mb-3">Invoice</h3>
                <table class="ms-auto text-end small">
                    <tr>
                        <td class="pe-3 text-muted">Referensi</td>
                        <td class="fw-semibold">{{ $invoice->referensi }}</td>
                    </tr>
                    <tr>
                        <td class="pe-3 text-muted">Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="pe-3 text-muted">Tgl. Jatuh Tempo</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Informasi Perusahaan & Tagihan Kepada -->
        <div class="row mb-4">
            <div class="col-6">
                <p class="fw-bold text-secondary mb-1 border-bottom pb-1" style="width: 200px;">Informasi Perusahaan</p>
                <h6 class="fw-bold text-primary mt-2">Rohman Store</h6>
                <p class="text-muted small mb-0">
                    JL. Suplir Taman Wisma Asri 2 Blok AA27/31, Teluk Pucung Bekasi Utara<br>
                    Telp: 089516339115<br>
                    Email: nursyaidr@gmail.com
                </p>
            </div>
            <div class="col-6">
                <p class="fw-bold text-secondary mb-1 border-bottom pb-1" style="width: 200px;">Tagihan Kepada</p>
                <h6 class="fw-bold text-primary mt-2">{{ $invoice->nama_pelanggan }}</h6>
                <p class="text-muted small mb-0">{{ $invoice->alamat_pelanggan ?? 'DKI Jakarta' }}</p>
            </div>
        </div>

        <!-- Tabel Item Barang -->
        <div class="table-responsive mb-4">
            <table class="table align-middle mb-0 border">
                <thead class="table-dark" style="background-color: #2c3e50 !important; -webkit-print-color-adjust: exact;">
                    <tr class="small text-uppercase text-white">
                        <th class="py-3 px-3" style="width: 45%;">Nama Barang</th>
                        <th class="py-3 px-3 text-center" style="width: 15%;">Qty</th>
                        <th class="py-3 px-3 text-end" style="width: 20%;">Harga</th>
                        <th class="py-3 px-3 text-end" style="width: 20%;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    @php
                    // Memecah teks deskripsi imei kembali menjadi array bersih
                    $imeis = [];
                    if (!empty($item['deskripsi_imei']) && $item['deskripsi_imei'] !== '-') {
                    $cleaned = str_replace(["\r", ","], "\n", $item['deskripsi_imei']);
                    $lines = explode("\n", $cleaned);
                    foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (!empty($trimmed)) {
                    $imeis[] = $trimmed;
                    }
                    }
                    }
                    @endphp
                    <!-- Baris 1: Nama Barang, Qty, Harga, Total Harga -->
                    <tr>
                        <td class="fw-bold px-3 py-2 text-dark">{{ $item['nama_barang'] }}</td>
                        <td class="text-center px-3 py-2">{{ $item['kuantitas'] }}</td>
                        <td class="text-end px-3 py-2">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                        <td class="text-end px-3 py-2 fw-bold">Rp {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                    <!-- Baris 2: Detail IMEI di dalam tabel yang sama (Maks 5 Kolom) -->
                    @if(count($imeis) > 0)
                    <tr>
                        <td colspan="4" class="px-3 pb-3 pt-1 bg-light border-top-0">
                            <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 4px 12px; font-family: monospace; font-size: 0.8rem;" class="text-secondary">
                                @foreach($imeis as $imei)
                                <div style="white-space: nowrap;">{{ $imei }}</div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Catatan / Bank & Total -->
        <div class="row justify-content-between align-items-start mt-3">
            <div class="col-6">
                <p class="fw-bold text-secondary mb-1 border-bottom pb-1" style="width: 100px;">Pesan</p>
                <p class="small mb-3">
                    <b>BCA</b><br>
                    7391441018<br>
                    Rochman Nursyaid
                </p>
                <p class="small text-muted mb-0">
                    <b>Terbilang:</b><br>
                    <i>{{ $invoice->terbilang }}</i>
                </p>
            </div>
            <div class="col-5">
                <table class="w-100 small mb-4">
                    <tr>
                        <td class="py-2 text-muted fw-semibold">Subtotal</td>
                        <td class="py-2 text-end fw-bold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-top border-dark">
                        <td class="py-3 fw-bold text-dark fs-6">Total</td>
                        <td class="py-3 text-end fw-bold text-dark fs-6 text-decoration-underline">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                    </tr>
                </table>

                <!-- Tanda Tangan -->
                <div class="text-end mt-4 pt-3">
                    <p class="small text-muted mb-4">Dengan Hormat,</p>
                    <p class="small fw-bold text-dark mb-0 text-decoration-underline">Tanda Tangan</p>
                    <p class="small text-muted">Finance Dept</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styling Khusus Ukuran A4 & Posisi Tengah -->
<style>
    /* Tampilan pada Layar Website (Simulasi Ukuran A4 di Tengah) */
    .invoice-container {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        box-sizing: border-box;
    }

    @media print {

        /* Sembunyikan seluruh layout web (navbar, sidebar, tombol) */
        body * {
            visibility: hidden;
        }

        /* Tampilkan hanya container invoice */
        .invoice-container,
        .invoice-container * {
            visibility: visible;
        }

        /* Atur ukuran mutlak kertas A4 dan posisikan di tengah halaman cetak */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .invoice-container {
            position: absolute;
            left: 50%;
            top: 0;
            transform: translateX(-50%);
            width: 210mm !important;
            min-height: 297mm !important;
            margin: 0 !important;
            padding: 15mm !important;
            /* Margin dalam kertas A4 */
            box-shadow: none !important;
            border: none !important;
        }

        /* Pastikan warna header tabel ikut tercetak */
        thead.table-dark {
            background-color: #2c3e50 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endsection