@extends('layouts.app')

@section('title', 'Histori Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Histori Penjualan & Arsip Invoice</h3>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th class="text-center" style="width: 60px;">Detail</th>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal Terbit</th>
                        <th>Total Tagihan</th>
                        <th>Total Profit</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    // Mengambil master data barang untuk referensi cadangan jika data invoice lama kosong
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

                    $sourceItems = !empty($rawPembelianData) && is_array($rawPembelianData)
                    ? $rawPembelianData
                    : (is_array($rawItems) ? $rawItems : []);

                    $totalProfitInvoice = 0;

                    foreach($sourceItems as $it) {
                    $namaBarangIt = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang');

                    // Cek dan ambil harga jual dari berbagai variasi key di database
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
                    // Fallback terakhir jika data benar-benar kosong, ambil dari master barang saat ini
                    $masterBrg = $masterBarangs[$namaBarangIt] ?? null;
                    $hargaJualHistori = (float) ($masterBrg->harga_jual ?? ($masterBrg->harga ?? 0));
                    }

                    // Cek IMEI / Serial
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

                    $modalIt = isset($it['total_modal']) ? (float) $it['total_modal'] : 0;

                    if (count($imeis) > 1) {
                    $hargaSatuanUnit = count($imeis) > 0 ? ($hargaJualHistori / count($imeis)) : $hargaJualHistori;
                    foreach ($imeis as $singleImei) {
                    $profitUnit = $hargaSatuanUnit - $modalIt;
                    $totalProfitInvoice += $profitUnit;

                    $displayItems[] = [
                    'nama_barang' => $namaBarangIt,
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                    'via' => $it['via'] ?? '-',
                    'detail_imei' => $singleImei,
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $modalIt,
                    'harga_jual' => $hargaSatuanUnit,
                    'total_profit' => $profitUnit,
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    } else {
                    $singleImei = count($imeis) === 1 ? $imeis[0] : '-';
                    $profitIt = isset($it['total_profit']) ? (float) $it['total_profit'] : ($hargaJualHistori - $modalIt);
                    $totalProfitInvoice += $profitIt;

                    $displayItems[] = [
                    'nama_barang' => $namaBarangIt,
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                    'via' => $it['via'] ?? '-',
                    'detail_imei' => $singleImei,
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $modalIt,
                    'harga_jual' => $hargaJualHistori,
                    'total_profit' => $profitIt,
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    }
                    @endphp

                    <tr>
                        <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvoice{{ $invoice->id }}" title="Lihat Rincian Barang">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </td>
                        <td><span class="fw-bold text-primary">{{ $invoice->referensi }}</span></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $invoice->nama_pelanggan }}</span>
                            @if(!empty($invoice->alamat_pelanggan))
                            <br><small class="text-muted">{{ $invoice->alamat_pelanggan }}</small>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d/m/Y') }}</td>
                        <td class="fw-bold text-success">Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}</td>
                        <!-- Tambahan Kolom Total Profit Per Invoice -->
                        <td class="fw-bold {{ $totalProfitInvoice >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($totalProfitInvoice, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-file-earmark-text"></i> Invoice
                            </a>
                        </td>
                    </tr>

                    <!-- Baris Rincian Lengkap (Dropdown) -->
                    <tr class="bg-light">
                        <td colspan="8" class="p-0 border-0">
                            <div class="collapse p-3" id="collapseInvoice{{ $invoice->id }}">
                                <div class="card card-body border bg-white shadow-sm mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-box-seam me-1"></i> Rincian Barang Terjual Per Unit (Invoice: {{ $invoice->referensi }})</h6>
                                        <span class="badge bg-light text-dark border fw-bold fs-6">
                                            Total Profit Invoice: <span class="{{ $totalProfitInvoice >= 0 ? 'text-primary' : 'text-danger' }}">Rp {{ number_format($totalProfitInvoice, 0, ',', '.') }}</span>
                                        </span>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 40px;">No</th>
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
                                                @forelse($displayItems as $pIdx => $pItem)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $pIdx + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $pItem['nama_barang'] }}</strong>
                                                        @if(!empty($pItem['nama_device']))
                                                        <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $pItem['nama_device'] }}</small>
                                                        @endif
                                                        <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $pItem['nama_toko'] }}</small>
                                                    </td>
                                                    <td>
                                                        @if(!empty($pItem['detail_imei']) && $pItem['detail_imei'] !== '-')
                                                        <span style="font-family: monospace; font-size: 0.8rem;" class="text-dark bg-light px-2 py-1 rounded border d-inline-block">
                                                            {{ $pItem['detail_imei'] }}
                                                        </span>
                                                        @else
                                                        <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
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
                                                    <td>{{ isset($pItem['tanggal_beli']) ? \Carbon\Carbon::parse($pItem['tanggal_beli'])->format('d/m/Y') : '-' }}</td>
                                                    <!-- Total Modal -->
                                                    <td class="fw-bold text-secondary">Rp {{ number_format($pItem['total_modal'], 0, ',', '.') }}</td>
                                                    <!-- Harga Jual -->
                                                    <td class="fw-bold text-success">Rp {{ number_format($pItem['harga_jual'], 0, ',', '.') }}</td>
                                                    <!-- Total Profit -->
                                                    <td class="fw-bold {{ $pItem['total_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                                        Rp {{ number_format($pItem['total_profit'], 0, ',', '.') }}
                                                    </td>
                                                    <td>
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
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted py-2">Tidak ada rincian barang.</td>
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
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada histori penjualan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection