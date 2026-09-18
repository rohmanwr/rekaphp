@extends('layouts.app')

@section('title', 'Histori Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Histori Penjualan (Invoice Terbit)</h3>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Tabel Histori Penjualan -->
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
                        <th class="text-center" style="width: 120px;">Aksi Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $index => $invoice)
                    @php
                    $displayItems = [];

                    // 1. Cek dari pembelian_data (Format Snapshot Baru)
                    if (!empty($invoice->pembelian_data) && is_array($invoice->pembelian_data)) {
                    foreach($invoice->pembelian_data as $it) {
                    $imeiVal = $it['detail_imei'] ?? ($it['imei'] ?? (is_array($it['imei_list'] ?? null) ? implode(', ', $it['imei_list']) : '-'));
                    $displayItems[] = [
                    'nama_barang' => $it['nama_barang'] ?? 'Barang',
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? '-',
                    'detail_imei' => $imeiVal,
                    'via' => $it['via'] ?? '-',
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $it['total_modal'] ?? ($it['jumlah'] ?? 0),
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    }
                    // 2. Cek dari items (Format Alternatif / Lama)
                    elseif (!empty($invoice->items) && is_array($invoice->items)) {
                    foreach($invoice->items as $it) {
                    $imeiVal = '';
                    if (isset($it['detail_imei']) && !empty($it['detail_imei'])) {
                    $imeiVal = $it['detail_imei'];
                    } elseif (isset($it['imei_list']) && is_array($it['imei_list'])) {
                    $imeiVal = implode(', ', $it['imei_list']);
                    } elseif (isset($it['imei']) && !empty($it['imei'])) {
                    $imeiVal = $it['imei'];
                    } else {
                    $imeiVal = '-';
                    }

                    $displayItems[] = [
                    'nama_barang' => $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang Invoice'),
                    'nama_device' => $it['nama_device'] ?? null,
                    'nama_toko' => $it['nama_toko'] ?? ($it['toko'] ?? '-'),
                    'detail_imei' => $imeiVal,
                    'via' => $it['via'] ?? '-',
                    'tanggal_beli' => $it['tanggal_beli'] ?? $invoice->tanggal,
                    'total_modal' => $it['total_modal'] ?? ($it['jumlah'] ?? ($invoice->total ?? 0)),
                    'file_lampiran' => $it['file_lampiran'] ?? [],
                    ];
                    }
                    }
                    @endphp

                    <!-- Baris Utama Invoice -->
                    <tr>
                        <td class="text-center fw-semibold text-muted">{{ $loop->iteration }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInvoice{{ $invoice->id }}" aria-expanded="false" aria-controls="collapseInvoice{{ $invoice->id }}" title="Lihat Rincian Barang">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </td>
                        <td>
                            <span class="fw-bold text-primary">{{ $invoice->referensi }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $invoice->nama_pelanggan }}</span>
                            @if(!empty($invoice->alamat_pelanggan))
                            <br><small class="text-muted">{{ $invoice->alamat_pelanggan }}</small>
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d/m/Y') }}</td>
                        <td class="fw-bold text-success">Rp {{ number_format($invoice->total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-sm btn-primary" title="Cetak / Lihat Invoice">
                                <i class="bi bi-file-earmark-text"></i> Invoice
                            </a>
                        </td>
                    </tr>

                    <!-- Baris Dropdown Rincian Barang -->
                    <tr class="bg-light">
                        <td colspan="7" class="p-0 border-0">
                            <div class="collapse p-3" id="collapseInvoice{{ $invoice->id }}">
                                <div class="card card-body border bg-white shadow-sm mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold text-secondary m-0"><i class="bi bi-box-seam me-1"></i> Rincian Barang Terjual dalam Invoice {{ $invoice->referensi }}</h6>
                                        <span class="badge bg-success-subtle text-success small">Data Otomatis Terhubung</span>
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
                                                    <th>Lampiran</th>
                                                    <th class="text-center" style="width: 80px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($displayItems as $pIdx => $pItem)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $pIdx + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $pItem['nama_barang'] ?? '-' }}</strong>
                                                        @if(!empty($pItem['nama_device']))
                                                        <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $pItem['nama_device'] }}</small>
                                                        @endif
                                                        <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $pItem['nama_toko'] ?? '-' }}</small>
                                                    </td>
                                                    <td>
                                                        @if(!empty($pItem['detail_imei']) && $pItem['detail_imei'] !== '-')
                                                        <span class="badge bg-light text-dark border font-monospace">{{ $pItem['detail_imei'] }}</span>
                                                        @else
                                                        <span class="text-muted small fst-italic">Tidak ada IMEI</span>
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
                                                    <td class="fw-bold">Rp {{ number_format($pItem['total_modal'] ?? 0, 0, ',', '.') }}</td>
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
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#modalEditHistori{{ $invoice->id }}_{{ $pIdx }}" title="Koreksi Data">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Modal Edit Rincian Item Histori -->
                                                <div class="modal fade" id="modalEditHistori{{ $invoice->id }}_{{ $pIdx }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content text-start">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">Koreksi Data Item ({{ $invoice->referensi }})</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('penjualan.update-histori-item', $invoice->id) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="item_index" value="{{ $pIdx }}">

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nama Barang</label>
                                                                        <input type="text" name="nama_barang" class="form-control" value="{{ $pItem['nama_barang'] ?? '' }}" required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nama Device (Opsional)</label>
                                                                        <input type="text" name="nama_device" class="form-control" value="{{ $pItem['nama_device'] ?? '' }}">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nama Toko Asal</label>
                                                                        <input type="text" name="nama_toko" class="form-control" value="{{ $pItem['nama_toko'] ?? '' }}" required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Via Pembelian</label>
                                                                        <input type="text" name="via" class="form-control" value="{{ $pItem['via'] ?? '' }}" required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Nomor IMEI / Serial</label>
                                                                        <input type="text" name="detail_imei" class="form-control font-monospace" value="{{ $pItem['detail_imei'] ?? '' }}">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Total Modal / Harga (Rp)</label>
                                                                        <input type="number" name="total_modal" class="form-control" value="{{ $pItem['total_modal'] ?? 0 }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-warning text-white fw-semibold">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-2">Tidak ada rincian barang.</td>
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
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada histori penjualan / invoice yang diterbitkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection