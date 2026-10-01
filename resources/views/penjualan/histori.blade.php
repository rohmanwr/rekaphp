@extends('layouts.app')

@section('title', 'Histori Penjualan')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .histori-invoice-scroll {
            overflow: visible;
        }

        .histori-invoice-shell {
            border: 0 !important;
            background: transparent;
            box-shadow: none !important;
        }

        .histori-invoice-table,
        .histori-invoice-table tbody {
            display: block;
            width: 100%;
        }

        .histori-invoice-table thead {
            display: none;
        }

        .histori-invoice-table tr.invoice-history-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            grid-template-areas:
                "number invoice"
                "customer customer"
                "date payment"
                "modal bill"
                "profit profit"
                "detail actions";
            gap: 0;
            margin: 0 0 14px;
            padding: 0;
            overflow: hidden;
            border: 1px solid #dce2e6;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 3px 10px rgba(33, 37, 41, 0.07);
        }

        .histori-invoice-table tr.invoice-history-row>td {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            padding: 11px 10px !important;
            border: 0 !important;
            border-right: 1px solid #dce2e6 !important;
            border-bottom: 1px solid #dce2e6 !important;
            overflow-wrap: anywhere;
            text-align: left !important;
        }

        .histori-invoice-table tr.invoice-history-row>td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(1) {
            grid-area: number;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(2) {
            grid-area: detail;
            align-items: flex-start;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(3) {
            grid-area: invoice;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(4) {
            grid-area: customer;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(5) {
            grid-area: date;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(6) {
            grid-area: payment;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(7) {
            grid-area: modal;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(8) {
            grid-area: bill;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(9) {
            grid-area: profit;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(10) {
            grid-area: actions;
            flex-direction: row;
            align-items: center;
            border-right: 0 !important;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(3) .fw-bold {
            font-size: 1rem;
            overflow-wrap: anywhere;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(4) .fw-semibold {
            color: #212529;
            font-size: 0.95rem;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(7),
        .histori-invoice-table tr.invoice-history-row>td:nth-child(8) {
            padding: 12px 10px !important;
            background: #f5f7f8;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(6) select {
            width: 100%;
            max-width: 100%;
            padding-right: 1.8rem;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(7),
        .histori-invoice-table tr.invoice-history-row>td:nth-child(8),
        .histori-invoice-table tr.invoice-history-row>td:nth-child(9) {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(10)::before {
            margin-right: auto;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(10) .d-flex {
            justify-content: flex-end !important;
            flex-wrap: wrap;
        }

        .histori-invoice-table tr.invoice-history-row>td:nth-child(10) .btn {
            min-width: 40px;
            min-height: 40px;
        }

        .histori-invoice-table tr.invoice-detail-row,
        .histori-invoice-table tr.invoice-empty-row {
            display: block;
            width: 100%;
        }

        .histori-invoice-table tr.invoice-detail-row>td,
        .histori-invoice-table tr.invoice-empty-row>td {
            display: block;
            width: 100%;
        }

        .histori-invoice-table tr.invoice-detail-row>td::before {
            content: none;
        }

        .histori-invoice-table tr.invoice-detail-row .collapse {
            padding: 8px !important;
        }

        .invoice-detail-card {
            min-width: 0;
            padding: 12px !important;
        }

        .invoice-detail-header {
            align-items: flex-start !important;
            flex-direction: column;
            gap: 8px;
        }

        .invoice-detail-header h6 {
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .invoice-detail-header .badge {
            max-width: 100%;
            white-space: normal;
            text-align: left;
        }

        .return-selection-panel>.d-flex {
            width: 100%;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .invoice-detail-row .table-responsive {
            overflow: visible;
        }

        .invoice-detail-table,
        .invoice-detail-table tbody {
            display: block;
            width: 100%;
        }

        .invoice-detail-table thead {
            display: none;
        }

        .invoice-detail-table tr.invoice-item-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            grid-template-areas:
                "product product"
                "imei imei"
                "via date"
                "modal price"
                "profit profit"
                "return attachment";
            margin: 0 0 10px;
            overflow: hidden;
            border: 1px solid #dce2e6;
            border-radius: 8px;
            background: #fff;
        }

        .invoice-detail-table tr.invoice-item-row>td {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            padding: 9px 8px !important;
            border: 0 !important;
            border-right: 1px solid #dce2e6 !important;
            border-bottom: 1px solid #dce2e6 !important;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .invoice-detail-table tr.invoice-item-row>td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .66rem;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(1) {
            display: none;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(2) {
            grid-area: return;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(3) {
            grid-area: product;
            border-right: 0 !important;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(4) {
            grid-area: imei;
            border-right: 0 !important;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(5) {
            grid-area: via;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(6) {
            grid-area: date;
            border-right: 0 !important;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(7) {
            grid-area: modal;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(8) {
            grid-area: price;
            border-right: 0 !important;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(9) {
            grid-area: profit;
            border-right: 0 !important;
            background: #f5f7f8;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(10) {
            grid-area: attachment;
            border-right: 0 !important;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(2) label {
            flex-wrap: wrap;
        }

        .invoice-detail-table tr.invoice-item-row>td:nth-child(10) .d-flex {
            flex-wrap: wrap;
        }

        .invoice-detail-table tr.invoice-item-empty-row {
            display: block;
        }

        .invoice-detail-table tr.invoice-item-empty-row>td {
            display: block;
            width: 100%;
            padding: 12px !important;
            border: 0 !important;
            text-align: center;
        }

        .histori-page-title {
            font-size: 1.2rem;
            line-height: 1.3;
        }

    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0 histori-page-title">Histori Penjualan & Arsip Invoice</h3>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm histori-invoice-shell">
    <div class="card-body p-0">
        <div class="table-responsive histori-invoice-scroll">
            <table class="table table-hover align-middle mb-0 histori-invoice-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center" style="width: 50px;">Detail</th>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal Terbit</th>
                        <th style="width: 160px;">Status Payment</th>
                        <th>Total Modal Keseluruhan</th>
                        <th>Total Tagihan</th>
                        <th>Total Profit</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $masterBarangs = \App\Models\Barang::all()->keyBy('nama_barang');
                    @endphp

                    @forelse($invoices as $index => $invoice)
                    @php
                    $displayItems = [];

                    $rawPembelianData = $invoice->pembelian_data;
                    if (is_string($rawPembelianData)) {
                    $rawPembelianData = json_decode($rawPembelianData, true);
                    }

                    $rawItems = $invoice->items;
                    if (is_string($rawItems)) {
                    $rawItems = json_decode($rawItems, true);
                    }
                    $rawItems = is_array($rawItems) ? array_values($rawItems) : [];

                    $sourceItems = !empty($rawPembelianData) && is_array($rawPembelianData)
                    ? $rawPembelianData
                    : (is_array($rawItems) ? $rawItems : []);

                    $totalProfitInvoice = 0;
                    $totalModalInvoice = 0;

                    foreach($sourceItems as $it) {
                    $namaBarangIt = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang');

                    $hargaJualHistori = 0;
                    if (isset($it['harga_jual']) && is_numeric($it['harga_jual']) && $it['harga_jual'] > 0) {
                    $hargaJualHistori = (float) $it['harga_jual'];
                    } elseif (isset($it['harga']) && is_numeric($it['harga']) && $it['harga'] > 0) {
                    $hargaJualHistori = (float) $it['harga'];
                    } elseif (isset($it['jumlah']) && isset($it['kuantitas']) && $it['kuantitas'] > 0) {
                    $hargaJualHistori = (float) ($it['jumlah'] / $it['kuantitas']);
                    } elseif (isset($it['jumlah']) && is_numeric($it['jumlah']) && $it['jumlah'] > 0) {
                    $hargaJualHistori = (float) $it['jumlah'];
                    } else {
                    $masterBrg = $masterBarangs[$namaBarangIt] ?? null;
                    $hargaJualHistori = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
                    }

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

                    $matchedInvoiceItemIndex = null;
                    $fallbackInvoiceItemIndex = null;
                    $snapshotPurchaseId = (int) ($it['pembelian_id'] ?? 0);
                    $snapshotPurchaseIds = is_array($it['pembelian_ids'] ?? null) ? array_map('intval', $it['pembelian_ids']) : [];
                    foreach (is_array($rawItems) ? $rawItems : [] as $invoiceItemIndex => $invoiceLine) {
                    if (!is_array($invoiceLine)) continue;

                    $invoicePurchaseIds = is_array($invoiceLine['pembelian_ids'] ?? null) ? array_map('intval', $invoiceLine['pembelian_ids']) : [];
                    if (($snapshotPurchaseId && in_array($snapshotPurchaseId, $invoicePurchaseIds, true))
                    || ($snapshotPurchaseIds && array_intersect($snapshotPurchaseIds, $invoicePurchaseIds))) {
                    $matchedInvoiceItemIndex = $invoiceItemIndex;
                    break;
                    }

                    $invoiceLineName = strtolower($invoiceLine['nama_barang'] ?? $invoiceLine['deskripsi'] ?? '');
                    if ($invoiceLineName !== strtolower($namaBarangIt)) continue;
                    if ($fallbackInvoiceItemIndex === null) $fallbackInvoiceItemIndex = $invoiceItemIndex;

                    $snapshotPrice = $it['harga_jual'] ?? $it['harga'] ?? null;
                    $invoiceLinePrice = $invoiceLine['harga'] ?? $invoiceLine['harga_jual'] ?? null;
                    if (is_numeric($snapshotPrice) && is_numeric($invoiceLinePrice) && (float) $snapshotPrice === (float) $invoiceLinePrice) {
                    $matchedInvoiceItemIndex = $invoiceItemIndex;
                    break;
                    }
                    }
                    if ($matchedInvoiceItemIndex === null) $matchedInvoiceItemIndex = $fallbackInvoiceItemIndex;

                    $modalIt = isset($it['total_modal']) ? (float) $it['total_modal'] : 0;

                    if (count($imeis) > 1) {
                    $totalModalInvoice += $modalIt * count($imeis);
                    $hargaSatuanUnit = count($imeis) > 0 ? ($hargaJualHistori / count($imeis)) : $hargaJualHistori;
                    foreach ($imeis as $singleImei) {
                    $profitUnit = $hargaSatuanUnit - $modalIt;
                    $totalProfitInvoice += $profitUnit;

                    $displayItems[] = [
                    'invoice_item_index' => $matchedInvoiceItemIndex,
                    'nama_barang' => $namaBarangIt,
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_alamat' => $it['nama_alamat'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                    'via' => $it['via'] ?? '-',
                    'titipan' => $it['titipan'] ?? 'Tidak',
                    'nama_trader' => $it['nama_trader'] ?? null,
                    'detail_imei' => $singleImei,
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $modalIt,
                    'harga_jual' => $hargaSatuanUnit,
                    'total_profit' => $profitUnit,
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    } else {
                    $totalModalInvoice += $modalIt;
                    $singleImei = count($imeis) === 1 ? $imeis[0] : '-';
                    $profitIt = isset($it['total_profit']) ? (float) $it['total_profit'] : ($hargaJualHistori - $modalIt);
                    $totalProfitInvoice += $profitIt;

                    $displayItems[] = [
                    'invoice_item_index' => $matchedInvoiceItemIndex,
                    'nama_barang' => $namaBarangIt,
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_alamat' => $it['nama_alamat'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                    'via' => $it['via'] ?? '-',
                    'titipan' => $it['titipan'] ?? 'Tidak',
                    'nama_trader' => $it['nama_trader'] ?? null,
                    'detail_imei' => $singleImei,
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $modalIt,
                    'harga_jual' => $hargaJualHistori,
                    'total_profit' => $profitIt,
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    }

                    $totalModalInvoice = max(0, $totalModalInvoice - (float) ($invoice->retur_total_modal ?? 0));
                    $totalProfitInvoice -= (float) ($invoice->retur_total_nilai ?? 0) - (float) ($invoice->retur_total_modal ?? 0);

                    $statusPayment = strtolower($invoice->status_payment ?? 'belum');
                    @endphp

                    <tr class="invoice-history-row">
                        <td class="text-center fw-semibold text-muted" data-label="No">{{ $loop->iteration }}</td>
                        <td class="text-center" data-label="Detail">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvoice{{ $invoice->id }}" title="Lihat Rincian Barang" aria-label="Lihat rincian barang">
                                <i class="bi bi-chevron-down me-1"></i>Rincian
                            </button>
                        </td>
                        <td data-label="No. Invoice"><span class="fw-bold text-primary">{{ $invoice->referensi }}</span></td>
                        <td data-label="Pelanggan">
                            <span class="fw-semibold text-dark">{{ $invoice->nama_pelanggan }}</span>
                            @if(!empty($invoice->alamat_pelanggan))
                            <br><small class="text-muted">{{ $invoice->alamat_pelanggan }}</small>
                            @endif
                        </td>
                        <td data-label="Tanggal Terbit">{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d/m/Y') }}</td>

                        <!-- DROPDOWN UPDATE STATUS PAYMENT -->
                        <td data-label="Status Payment">
                            @if($invoice->is_locked)
                            <span class="badge {{ $statusPayment === 'sudah' ? 'bg-success' : 'bg-warning text-dark' }}">
                                <i class="bi bi-lock-fill me-1"></i>{{ $statusPayment === 'sudah' ? 'Sudah' : 'Belum' }} - Terkunci
                            </span>
                            @else
                            <form id="formPayment{{ $invoice->id }}" action="{{ route('penjualan.updatePaymentStatus', $invoice->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status_payment" class="form-select form-select-sm rounded-pill fw-bold {{ $statusPayment === 'sudah' ? 'bg-success text-white' : 'bg-warning text-dark' }}" onchange="document.getElementById('formPayment{{ $invoice->id }}').submit()">
                                    <option value="belum" {{ $statusPayment === 'belum' ? 'selected' : '' }}>Belum</option>
                                    <option value="sudah" {{ $statusPayment === 'sudah' ? 'selected' : '' }}>Sudah</option>
                                </select>
                            </form>
                            @endif
                            @if($statusPayment === 'sudah' && !empty($invoice->tanggal_payment))
                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">
                                Paid: {{ \Carbon\Carbon::parse($invoice->tanggal_payment)->format('d/m/Y') }}
                            </small>
                            @endif
                        </td>

                        <td class="fw-bold text-secondary" data-label="Total Modal Keseluruhan">Rp {{ number_format($totalModalInvoice, 0, ',', '.') }}</td>
                        <td class="fw-bold text-success" data-label="Total Tagihan">
                            <span>Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}</span>
                            @if(($invoice->retur_total_nilai ?? 0) > 0)
                            <small class="d-block fw-normal text-danger">Retur: -Rp {{ number_format($invoice->retur_total_nilai, 0, ',', '.') }}</small>
                            <small class="d-block fw-semibold text-dark">Bersih: Rp {{ number_format(max(0, (float) $invoice->total - (float) $invoice->retur_total_nilai), 0, ',', '.') }}</small>
                            @endif
                        </td>

                        <!-- TOTAL PROFIT (Hanya bernilai jika status payment 'sudah') -->
                        <td class="fw-bold {{ $statusPayment === 'sudah' ? ($totalProfitInvoice >= 0 ? 'text-primary' : 'text-danger') : 'text-muted' }}" data-label="Total Profit">
                            @if($statusPayment === 'sudah')
                            Rp {{ number_format($totalProfitInvoice, 0, ',', '.') }}
                            @else
                            <span class="badge bg-secondary">Rp 0 (Pending)</span>
                            @endif
                        </td>

                        <!-- AKSI: PRINT INVOICE & TOMBOL DELETE INVOICE -->
                        <td class="text-center" data-label="Aksi">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-primary" title="Lihat/Print Invoice">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>

                                @if($invoice->is_locked)
                                <a href="{{ route('penjualan.retur.index', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-outline-warning" title="Catat atau lihat retur barang">
                                    <i class="bi bi-arrow-return-left"></i>
                                </a>
                                <span class="badge bg-secondary align-content-center" title="Invoice terkunci">
                                    <i class="bi bi-lock-fill"></i> Terkunci
                                </span>
                                @elseif($statusPayment !== 'sudah')
                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Ubah Status Payment menjadi Sudah untuk mengunci invoice" aria-label="Invoice belum dapat dikunci" disabled>
                                    <i class="bi bi-lock"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusInvoice{{ $invoice->id }}" title="Hapus Invoice">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @else
                                <form action="{{ route('invoice.lock', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Kunci invoice ini? Setelah dikunci, data tidak dapat diubah atau dihapus.');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-dark" title="Kunci Data Invoice" aria-label="Kunci data invoice">
                                        <i class="bi bi-lock"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusInvoice{{ $invoice->id }}" title="Hapus Invoice">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Baris Rincian Lengkap (Dropdown Collapse) -->
                    <tr class="bg-light invoice-detail-row">
                        <td colspan="10" class="p-0 border-0">
                            <div class="collapse p-3" id="collapseInvoice{{ $invoice->id }}">
                                <div class="card card-body border bg-white shadow-sm mb-2 invoice-detail-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3 invoice-detail-header">
                                        <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-box-seam me-1"></i> Rincian Barang Terjual Per Unit (Invoice: {{ $invoice->referensi }})</h6>
                                        <span class="badge bg-light text-dark border fw-bold fs-6">
                                            Total Profit Realized:
                                            <span class="{{ $statusPayment === 'sudah' ? 'text-primary' : 'text-muted' }}">
                                                Rp {{ number_format($statusPayment === 'sudah' ? $totalProfitInvoice : 0, 0, ',', '.') }}
                                            </span>
                                        </span>
                                    </div>

                                    @if($invoice->is_locked)
                                    <div class="return-selection-panel d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 border rounded p-3 mb-3 bg-light"
                                        data-invoice-id="{{ $invoice->id }}"
                                        data-return-url="{{ route('penjualan.retur.index', ['invoice_id' => $invoice->id]) }}">
                                        <div>
                                            <div class="fw-bold"><i class="bi bi-check2-square text-success me-1"></i>Pilih unit yang akan diretur</div>
                                            <small class="text-muted" data-selection-message>Pilih satu atau beberapa unit dari barang yang sama.</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-white text-dark border" data-selection-count>0 unit dipilih</span>
                                            <button type="button" class="btn btn-sm btn-success fw-semibold" data-process-return disabled>
                                                Proses retur
                                            </button>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0 invoice-detail-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 40px;">No</th>
                                                    <th class="text-center" style="width: 88px;">Retur</th>
                                                    <th>Barang & Toko</th>
                                                    <th>IMEI / Serial</th>
                                                    <th>Via</th>
                                                    <th>Tgl Beli</th>
                                                    <th>Total Modal</th>
                                                    <th>Harga Jual</th>
                                                    <th>Total Profit</th>
                                                    <th>Lampiran</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $displayUnitCounters = []; @endphp
                                                @forelse($displayItems as $pIdx => $pItem)
                                                @php
                                                $invoiceItemIndex = $pItem['invoice_item_index'] ?? null;
                                                $unitImei = $pItem['detail_imei'] ?? '-';
                                                $lineReturns = $invoiceItemIndex !== null ? $invoice->returns->where('item_index', $invoiceItemIndex) : collect();
                                                $lineReturnedQuantity = (int) $lineReturns->sum('kuantitas');
                                                $lineReturnedImeis = [];
                                                foreach ($lineReturns as $lineReturn) {
                                                $lineImeis = preg_split('/[\r\n,]+/', $lineReturn->detail_imei ?? '') ?: [];
                                                $lineImeis = array_values(array_filter(array_map('trim', $lineImeis)));
                                                $lineReturnedImeis = array_merge($lineReturnedImeis, array_map('strtolower', $lineImeis));
                                                }
                                                $unitPosition = $invoiceItemIndex !== null ? ($displayUnitCounters[$invoiceItemIndex] ?? 0) : 0;
                                                if ($invoiceItemIndex !== null) $displayUnitCounters[$invoiceItemIndex] = $unitPosition + 1;
                                                $unitIsReturned = $unitImei !== '-'
                                                ? (in_array(strtolower($unitImei), $lineReturnedImeis, true) || (count($lineReturnedImeis) === 0 && $unitPosition < $lineReturnedQuantity))
                                                    : $unitPosition < $lineReturnedQuantity;
                                                    @endphp
                                                    <tr class="invoice-item-row">
                                                    <td class="text-center text-muted" data-label="No">{{ $pIdx + 1 }}</td>
                                                    <td class="text-center" data-label="Status Retur">
                                                        @if($invoice->is_locked && $invoiceItemIndex !== null)
                                                        <label class="d-inline-flex align-items-center gap-1 small {{ $unitIsReturned ? 'text-success' : 'text-primary' }}">
                                                            <input type="checkbox" class="form-check-input m-0 return-select-checkbox" @checked($unitIsReturned) @disabled($unitIsReturned)
                                                                data-invoice-id="{{ $invoice->id }}"
                                                                data-item-index="{{ $invoiceItemIndex }}"
                                                                data-imei="{{ $unitImei !== '-' ? $unitImei : '' }}"
                                                                aria-label="{{ $unitIsReturned ? 'IMEI ' . $unitImei . ' sudah diretur' : ($unitImei !== '-' ? 'Pilih IMEI ' . $unitImei . ' untuk retur' : 'Pilih unit untuk retur') }}">
                                                            <span>{{ $unitIsReturned ? 'Diretur' : 'Pilih' }}</span>
                                                        </label>
                                                        @else
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td data-label="Barang & Toko">
                                                        <strong>{{ $pItem['nama_barang'] }}</strong>
                                                        @if(!empty($pItem['nama_device']))
                                                        <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $pItem['nama_device'] }}</small>
                                                        @endif
                                                        <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $pItem['nama_toko'] }}</small>
                                                        <br><small class="text-muted">Titipan: {{ $pItem['titipan'] ?? 'Tidak' }}</small>
                                                        <br><small class="text-muted">Trader: {{ $pItem['nama_trader'] ?: '-' }}</small>
                                                        @if(!empty($pItem['nama_alamat']))
                                                        <br><small class="text-danger"><i class="bi bi-geo-alt"></i> {{ $pItem['nama_alamat'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td data-label="IMEI / Serial">
                                                        @if(!empty($pItem['detail_imei']) && $pItem['detail_imei'] !== '-')
                                                        <span style="font-family: monospace; font-size: 0.8rem;" class="text-dark bg-light px-2 py-1 rounded border d-inline-block">
                                                            {{ $pItem['detail_imei'] }}
                                                        </span>
                                                        @else
                                                        <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td data-label="Via">
                                                        @php
                                                        $viaVal = $pItem['via'] ?? '-';
                                                        $badgeColor = match($viaVal) {
                                                        'Tokopedia' => 'bg-success',
                                                        'Shopee' => 'bg-warning text-dark',
                                                        'Lazada' => 'bg-primary',
                                                        'TikTok' => 'bg-dark',
                                                        default => 'bg-secondary'
                                                        };
                                                        @endphp
                                                        <span class="badge {{ $badgeColor }}">{{ $viaVal }}</span>
                                                    </td>
                                                    <td data-label="Tgl Beli">{{ isset($pItem['tanggal_beli']) ? \Carbon\Carbon::parse($pItem['tanggal_beli'])->format('d/m/Y') : '-' }}</td>
                                                    <td class="fw-bold text-secondary" data-label="Total Modal">Rp {{ number_format($pItem['total_modal'], 0, ',', '.') }}</td>
                                                    <td class="fw-bold text-success" data-label="Harga Jual">Rp {{ number_format($pItem['harga_jual'], 0, ',', '.') }}</td>
                                                    <td class="fw-bold {{ $statusPayment === 'sudah' ? 'text-primary' : 'text-muted' }}" data-label="Total Profit">
                                                        Rp {{ number_format($statusPayment === 'sudah' ? $pItem['total_profit'] : 0, 0, ',', '.') }}
                                                    </td>
                                                    <td data-label="Lampiran">
                                                        @if(!empty($pItem['file_lampiran']) && is_array($pItem['file_lampiran']) && count($pItem['file_lampiran']) > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($pItem['file_lampiran'] as $idx => $filePath)
                                                            @php $ext = pathinfo($filePath, PATHINFO_EXTENSION); @endphp
                                                            <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="btn btn-xs btn-outline-info p-1 px-2 text-decoration-none" style="font-size: 0.70rem;">
                                                                <i class="bi {{ strtolower($ext) == 'pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary' }}"></i>
                                                                File {{ $idx + 1 }}
                                                            </a>
                                                            @endforeach
                                                        </div>
                                                        @else
                                                        <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                    </tr>
                    @empty
                    <tr class="invoice-item-empty-row">
                        <td colspan="10" class="text-center text-muted py-2">Tidak ada rincian barang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</td>
</tr>

@empty
<tr class="invoice-empty-row">
    <td colspan="10" class="text-center py-4 text-muted">Belum ada histori penjualan.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>

<!-- CONTAINER MODAL HAPUS INVOICE (DILUAR TABLE AGAR TIDAK TERJADI BREAKDOWN HTML) -->
@foreach($invoices as $invoice)
@if(!$invoice->is_locked)
<div class="modal fade" id="modalHapusInvoice{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Konfirmasi Hapus Invoice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('invoice.destroy', $invoice->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body p-3">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus data invoice berikut?</p>
                    <div class="p-2 bg-light border rounded small">
                        <strong>No. Invoice:</strong> {{ $invoice->referensi }}<br>
                        <strong>Pelanggan:</strong> {{ $invoice->nama_pelanggan }}<br>
                        <strong>Total Tagihan:</strong> Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}
                    </div>
                    <small class="text-danger mt-2 d-block fs-7">
                        *Tindakan ini menghapus arsip invoice secara permanen dan mengubah status barang terkait menjadi Bermasalah.
                    </small>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger fw-bold">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<script>
    (() => {
        const checkboxes = Array.from(document.querySelectorAll('.return-select-checkbox'));

        document.querySelectorAll('.return-selection-panel').forEach(panel => {
            const invoiceId = panel.dataset.invoiceId;
            const invoiceCheckboxes = checkboxes.filter(checkbox => checkbox.dataset.invoiceId === invoiceId);
            const processButton = panel.querySelector('[data-process-return]');
            const countLabel = panel.querySelector('[data-selection-count]');
            const message = panel.querySelector('[data-selection-message]');

            const selectedUnits = () => invoiceCheckboxes.filter(checkbox => checkbox.checked && !checkbox.disabled);

            function refreshSelection() {
                const selected = selectedUnits();
                countLabel.textContent = `${selected.length} unit dipilih`;
                processButton.disabled = selected.length === 0;
                processButton.textContent = selected.length ? `Proses retur (${selected.length})` : 'Proses retur';

                if (selected.length === 0) {
                    message.textContent = 'Pilih satu atau beberapa unit dari barang yang sama.';
                }
            }

            invoiceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const selected = selectedUnits();
                    const selectedLine = selected[0]?.dataset.itemIndex;
                    if (checkbox.checked && selected.some(unit => unit.dataset.itemIndex !== selectedLine)) {
                        checkbox.checked = false;
                        message.textContent = 'Satu pengajuan hanya untuk satu jenis barang. Hapus pilihan sebelumnya untuk mengganti barang.';
                        refreshSelection();
                        return;
                    }

                    refreshSelection();
                });
            });

            processButton.addEventListener('click', () => {
                const selected = selectedUnits();
                if (!selected.length) return;

                const target = new URL(panel.dataset.returnUrl, window.location.href);
                target.searchParams.set('item_index', selected[0].dataset.itemIndex);
                target.searchParams.set('kuantitas', String(selected.length));
                const imeis = selected.map(unit => unit.dataset.imei).filter(Boolean);
                if (imeis.length) target.searchParams.set('detail_imei', imeis.join('\n'));
                window.location.assign(target.toString());
            });

            refreshSelection();
        });
    })();
</script>

@endsection