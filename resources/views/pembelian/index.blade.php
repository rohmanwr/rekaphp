@extends('layouts.app')

@section('title', 'Rekap Pembelian HP')

@section('content')
<!-- Library Scanner Barcode HTML5 -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<!-- Library Panzoom & Hammer.js untuk fitur Zoom dan Geser Gambar -->
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

<!-- Header & Navigation Bar -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <h3 class="fw-bold text-dark mb-0">Rekap Pembelian</h3>
    <div class="d-grid d-sm-flex align-items-center gap-2 w-100 w-md-auto">
        <!-- Tombol Terpisah 1: Rekap Total Kuantitas Barang per Toko -->
        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold py-2" data-bs-toggle="modal" data-bs-target="#modalTotalBarangPerToko" title="Lihat Total Kuantitas Barang per Toko">
            <i class="bi bi-box-seam-fill me-1"></i> Total Barang
        </button>

        <!-- Tombol Terpisah 2: Ringkasan Salin Pesanan Checklist -->
        <button type="button" class="btn btn-outline-dark btn-sm fw-semibold py-2" data-bs-toggle="modal" data-bs-target="#modalRingkasanSalin" title="Salin Ringkasan Pesanan">
            <i class="bi bi-clipboard-check me-1"></i> Salin Ringkasan
        </button>

        <!-- Tombol Terpisah 3: Cek IMEI Duplikat -->
        <button type="button" class="btn btn-warning btn-sm fw-semibold text-dark shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#modalCekDuplikatImeiIndex" onclick="jalankanCekDuplikatImeiIndex()">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Cek Duplikat IMEI
        </button>

        <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm fw-bold py-2">
            <i class="bi bi-plus-lg me-1"></i> Tambah Beli
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Searchbar Filter & Filter Status dengan Jumlah Total -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form id="searchForm" action="{{ route('pembelian.index') }}" method="GET">
            @if(!empty($selectedStatus))
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari TRX, Barang, Toko, IMEI..."
                    value="{{ $search }}"
                    autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('pembelian.index', ['status' => $selectedStatus]) }}" class="btn btn-outline-secondary" title="Reset Pencarian Teks">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>

        @php
        // Menghitung jumlah total data berdasarkan masing-masing status
        $baseCountQuery = \App\Models\Pembelian::whereNotIn('status', ['Jual', 'Selesai']);
        if(!empty($search)) {
        $baseCountQuery->where(function ($q) use ($search) {
        $q->where('kode_manual', 'like', "%{$search}%")
        ->orWhere('nama_alamat', 'like', "%{$search}%")
        ->orWhere('nama_barang', 'like', "%{$search}%")
        ->orWhere('nama_toko', 'like', "%{$search}%")
        ->orWhere('detail_imei', 'like', "%{$search}%");
        });
        }

        $countSemua = (clone $baseCountQuery)->count();
        $countBelumReady = (clone $baseCountQuery)->where('status', 'Belum Ready')->count();
        $countSudahReady = (clone $baseCountQuery)->where('status', 'Sudah Ready')->count();
        $countSudahDiambil= (clone $baseCountQuery)->where('status', 'Sudah Diambil')->count();
        $countBermasalah = (clone $baseCountQuery)->where('status', 'Bermasalah')->count();
        $countJual = \App\Models\Pembelian::where('status', 'Jual')->count();
        $countSelesai = \App\Models\Pembelian::where('status', 'Selesai')->count();
        $countRetur = \App\Models\Pembelian::where('status', 'Retur')->count();
        @endphp

        <!-- Filter Tombol Status Beserta Jumlah (Mobile Horizontal Scrollable) -->
        <div class="d-flex flex-nowrap overflow-x-auto align-items-center gap-2 pb-2">
            <span class="fw-semibold text-muted small flex-shrink-0"><i class="bi bi-funnel-fill"></i> Filter:</span>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ empty($selectedStatus) ? 'btn-dark' : 'btn-outline-secondary' }}">
                Semua <span class="badge bg-light text-dark ms-1">{{ $countSemua }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Belum Ready'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Belum Ready' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">
                ⏳ Belum Ready <span class="badge bg-dark text-white ms-1">{{ $countBelumReady }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Ready'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Sudah Ready' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                ✅ Sudah Ready <span class="badge bg-light text-dark ms-1">{{ $countSudahReady }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Diambil'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Sudah Diambil' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                📦 Sudah Diambil <span class="badge bg-light text-dark ms-1">{{ $countSudahDiambil }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Bermasalah'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Bermasalah' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                ⚠️ Bermasalah <span class="badge bg-light text-dark ms-1">{{ $countBermasalah }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Jual'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Jual' ? 'btn-info text-white fw-bold' : 'btn-outline-info text-dark' }}">
                🏷️ Jual <span class="badge bg-light text-dark ms-1">{{ $countJual }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Selesai'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Selesai' ? 'btn-secondary text-white fw-bold' : 'btn-outline-secondary' }}">
                🏁 Selesai <span class="badge bg-light text-dark ms-1">{{ $countSelesai }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Retur'])) }}"
                class="btn btn-sm rounded-pill flex-shrink-0 {{ $selectedStatus == 'Retur' ? 'btn-danger text-white fw-bold' : 'btn-outline-danger' }}">
                <i class="bi bi-arrow-return-left me-1"></i>Retur <span class="badge bg-light text-dark ms-1">{{ $countRetur }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Container Form Terpisah untuk Quick Status Update Single Item -->
@foreach($pembelians as $item)
<form id="formStatusSingle{{ $item->id }}" action="{{ route('pembelian.updateStatus', $item->id) }}" method="POST" class="d-none">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="statusSingleValue{{ $item->id }}" value="{{ $item->status }}">
</form>
@endforeach

<!-- Form Update Status Massal -->
<form action="{{ route('pembelian.updateStatusMassal') }}" method="POST" id="formMassal">
    @csrf
    @method('PATCH')

    <!-- TAMPILAN 1: Desktop/Tablet (Tabel Standar) -->
    <div class="card border-0 shadow-sm mb-4 d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablePembelianIndex">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Pilih Semua">
                            </th>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>No Pesanan / Nama / Trader</th>
                            <th>Barang & Toko</th>
                            <th>IMEI / Serial</th>
                            <th>Via</th>
                            <th>Tgl Beli</th>
                            <th>Total Modal</th>
                            <th style="width: 150px;">Status</th>
                            <th>Lampiran</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembelians as $index => $item)
                        <tr class="row-pembelian-index row-pembelian-desktop"
                            data-detail-imei="{{ trim($item->detail_imei ?? '') }}"
                            data-kode-otomatis="{{ $item->kode_otomatis }}"
                            data-kode-manual="{{ $item->kode_manual ?? '-' }}"
                            data-nama-barang="{{ trim(($item->nama_device ?: $item->nama_barang)) }}"
                            data-nama-toko="{{ $item->nama_toko }}"
                            data-nama-alamat="{{ $item->nama_alamat ?? '-' }}"
                            data-tanggal-beli="{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}"
                            data-status="{{ $item->status }}">
                            <td class="text-center">
                                <input type="checkbox" name="pembelian_ids[]" value="{{ $item->id }}" class="form-check-input item-checkbox item-checkbox-desktop" data-id="{{ $item->id }}">
                            </td>
                            <td class="text-center fw-semibold text-muted">
                                @if(method_exists($pembelians, 'firstItem'))
                                {{ $pembelians->firstItem() + $index }}
                                @else
                                {{ $loop->iteration }}
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $item->kode_manual ?? '-' }}</span>
                                @if(!empty($item->nama_alamat))
                                <br><small class="text-muted"><i class="bi bi-geo-alt"></i> {{ $item->nama_alamat }}</small>
                                @endif
                                <br><small class="text-muted">Trader: {{ $item->nama_trader ?: '-' }}</small>
                            </td>
                            <td>
                                <strong>{{ $item->nama_barang }}</strong>
                                <br><small class="text-muted">Titipan: {{ $item->titipan ?? 'Tidak' }}</small>
                                @if(!empty($item->nama_device))
                                <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $item->nama_device }}</small>
                                @endif
                                <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
                            </td>
                            <td>
                                @if(!empty($item->detail_imei))
                                <span class="badge bg-light text-dark border font-monospace text-break" style="white-space: pre-line;">{{ $item->detail_imei }}</span>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeColor = match($item->via) {
                                'Tokopedia' => 'bg-success',
                                'Shopee' => 'bg-warning text-dark',
                                'Lazada' => 'bg-primary',
                                'TikTok' => 'bg-dark',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $badgeColor }}">{{ $item->via }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</td>
                            <td class="fw-bold">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
                            <td>
                                <select form="formStatusSingle{{ $item->id }}" class="form-select form-select-sm rounded-pill fw-semibold" onchange="document.getElementById('statusSingleValue{{ $item->id }}').value=this.value; document.getElementById('formStatusSingle{{ $item->id }}').submit()" style="width: fit-content; min-width: 140px;">
                                    @if($item->status == 'Retur')<option value="Retur" selected disabled>↩️ Retur</option>@endif
                                    <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                                    <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                                    <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                                    <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                                    <option value="Jual" {{ $item->status == 'Jual' ? 'selected' : '' }}>🏷️ Jual</option>
                                    <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>🏁 Selesai</option>
                                </select>
                            </td>
                            <td>
                                @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($item->file_lampiran as $idx => $filePath)
                                    @php
                                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                    $fileUrl = asset('storage/' . $filePath);
                                    @endphp
                                    <button type="button"
                                        class="btn btn-xs btn-outline-info p-1 px-2 text-decoration-none preview-btn"
                                        style="font-size: 0.75rem;"
                                        data-url="{{ $fileUrl }}"
                                        data-ext="{{ $ext }}"
                                        data-title="Lampiran {{ $idx + 1 }} ({{ $item->kode_manual ?? $item->kode_otomatis }})">
                                        <i class="bi {{ $ext == 'pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary' }}"></i>
                                        File {{ $idx + 1 }}
                                    </button>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditPembelian{{ $item->id }}" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <!-- Form Hapus Independen per Item -->
                                    <button type="submit" form="formDestroySingle{{ $item->id }}" class="btn btn-sm btn-danger fw-semibold" title="Hapus Transaksi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">Tidak ada data rekap pembelian.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAMPILAN 2: Mode HP/Seluler Modern Card -->
    <div class="d-block d-md-none mb-5">
        <!-- Panel Opsi "Pilih Semua" khusus Tampilan HP -->
        @if(count($pembelians) > 0)
        <div class="card border-0 shadow-sm mb-3 bg-light">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="form-check m-0 d-flex align-items-center gap-2">
                    <input type="checkbox" id="selectAllMobile" class="form-check-input my-0 fs-5">
                    <label for="selectAllMobile" class="form-check-label fw-bold text-dark small">Pilih Semua Item</label>
                </div>
                <span class="text-muted small">Total: {{ count($pembelians) }} Item</span>
            </div>
        </div>
        @endif

        @forelse($pembelians as $index => $item)
        @php
        $badgeColor = match($item->via) {
        'Tokopedia' => 'bg-success',
        'Shopee' => 'bg-warning text-dark',
        'Lazada' => 'bg-primary',
        'TikTok' => 'bg-dark',
        default => 'bg-secondary'
        };
        @endphp
        <div class="card border-0 shadow-sm mb-3 row-pembelian-index row-pembelian-mobile"
            data-detail-imei="{{ trim($item->detail_imei ?? '') }}"
            data-kode-otomatis="{{ $item->kode_otomatis }}"
            data-kode-manual="{{ $item->kode_manual ?? '-' }}"
            data-nama-barang="{{ trim(($item->nama_device ?: $item->nama_barang)) }}"
            data-nama-toko="{{ $item->nama_toko }}"
            data-nama-alamat="{{ $item->nama_alamat ?? '-' }}"
            data-tanggal-beli="{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}"
            data-status="{{ $item->status }}">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Checkbox Pilihan Massal per Card Mobile (Disabled Name agar tidak terkirim ganda) -->
                        <input type="checkbox" value="{{ $item->id }}" class="form-check-input item-checkbox item-checkbox-mobile mt-0 fs-5" data-id="{{ $item->id }}">
                        <span class="badge bg-light text-dark border font-monospace">{{ $item->kode_manual ?? $item->kode_otomatis }}</span>
                    </div>
                    <span class="badge {{ $badgeColor }}">{{ $item->via }}</span>
                </div>

                <div class="mb-2">
                    <h6 class="fw-bold mb-1 text-dark">{{ $item->nama_barang }}</h6>
                    <div class="text-muted small mb-1">Titipan: {{ $item->titipan ?? 'Tidak' }}</div>
                    @if(!empty($item->nama_device))
                    <div class="text-primary fw-semibold small mb-1"><i class="bi bi-phone me-1"></i>{{ $item->nama_device }}</div>
                    @endif
                    <div class="text-muted small"><i class="bi bi-shop me-1"></i>{{ $item->nama_toko }} @if(!empty($item->nama_alamat)) | <i class="bi bi-geo-alt me-1"></i>{{ $item->nama_alamat }} @endif</div>
                </div>

                @if(!empty($item->detail_imei))
                <div class="bg-light rounded p-2 border mb-2 font-monospace small text-break" style="white-space: pre-line;">
                    <i class="bi bi-barcode me-1 text-muted"></i>{{ $item->detail_imei }}
                </div>
                @endif

                <div class="d-flex justify-content-between align-items-center my-2">
                    <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</span>
                    <span class="fw-bold text-dark fs-6">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</span>
                </div>
                <div class="text-muted small mb-2">Trader: {{ $item->nama_trader ?: '-' }}</div>

                @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                <div class="mb-2 d-flex flex-wrap gap-1">
                    @foreach($item->file_lampiran as $idx => $filePath)
                    @php
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $fileUrl = asset('storage/' . $filePath);
                    @endphp
                    <button type="button" class="btn btn-xs btn-outline-info p-1 px-2 text-decoration-none preview-btn" style="font-size: 0.75rem;" data-url="{{ $fileUrl }}" data-ext="{{ $ext }}" data-title="Lampiran {{ $idx + 1 }}">
                        <i class="bi {{ $ext == 'pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary' }}"></i> File {{ $idx + 1 }}
                    </button>
                    @endforeach
                </div>
                @endif

                <div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top mt-2">
                    <!-- Dropdown Quick Update Status Mobile -->
                    <select form="formStatusSingle{{ $item->id }}" class="form-select form-select-sm rounded-pill fw-semibold w-auto" onchange="document.getElementById('statusSingleValue{{ $item->id }}').value=this.value; document.getElementById('formStatusSingle{{ $item->id }}').submit()">
                        @if($item->status == 'Retur')<option value="Retur" selected disabled>↩️ Retur</option>@endif
                        <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                        <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                        <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                        <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                        <option value="Jual" {{ $item->status == 'Jual' ? 'selected' : '' }}>🏷️ Jual</option>
                        <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>🏁 Selesai</option>
                    </select>

                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditPembelian{{ $item->id }}">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <button type="submit" form="formDestroySingle{{ $item->id }}" class="btn btn-sm btn-danger fw-semibold">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm p-4 text-center text-muted">
            Tidak ada data rekap pembelian.
        </div>
        @endforelse
    </div>

    <!-- Floating Action Bar untuk Update Status Massal (Mobile & Desktop) -->
    <div id="bulkActionBar" class="fixed-bottom bg-dark text-white p-2 p-md-3 shadow-lg d-none" style="z-index: 1050;">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small">
                <span id="selectedCount" class="fw-bold text-warning">0</span> item dipilih:
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-light btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditMassal">
                    <i class="bi bi-pencil-square me-1"></i>Edit Data Batch
                </button>
                <select name="status_massal" class="form-select form-select-sm" style="min-width: 140px;" required>
                    <option value="" disabled selected>-- Status Baru --</option>
                    <option value="Belum Ready">⏳ Belum Ready</option>
                    <option value="Sudah Ready">✅ Sudah Ready</option>
                    <option value="Sudah Diambil">📦 Sudah Diambil</option>
                    <option value="Bermasalah">⚠️ Bermasalah</option>
                    <option value="Jual">🏷️ Jual</option>
                    <option value="Selesai">🏁 Selesai</option>
                </select>
                <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold px-3">Terapkan</button>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modalEditMassal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Banyak Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('pembelian.updateMassal') }}" method="POST" id="formEditMassal">
                @csrf
                @method('PATCH')
                <div id="editMassalSelectedIds"></div>
                <div class="modal-body">
                    <p class="small text-muted"><span id="editMassalSelectedCount" class="fw-bold text-dark">0</span> barang dipilih. Centang field yang ingin diterapkan ke semua barang terpilih; field lain tidak akan diubah.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="nama_trader" data-target="bulkNamaTrader" id="bulkFieldTrader"><label class="form-check-label fw-semibold" for="bulkFieldTrader">Nama Trader</label></div>
                            <input type="text" name="nama_trader" id="bulkNamaTrader" class="form-control" placeholder="Nama trader baru atau kosongkan" disabled>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="titipan" data-target="bulkTitipan" id="bulkFieldTitipan"><label class="form-check-label fw-semibold" for="bulkFieldTitipan">Titipan</label></div>
                            <select name="titipan" id="bulkTitipan" class="form-select" disabled>
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                                <option value="Ya, (tidak ambil untung)">Ya, (tidak ambil untung)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="nama_toko" data-target="bulkNamaToko" id="bulkFieldToko"><label class="form-check-label fw-semibold" for="bulkFieldToko">Nama Toko</label></div>
                            <input type="text" name="nama_toko" id="bulkNamaToko" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="via" data-target="bulkVia" id="bulkFieldVia"><label class="form-check-label fw-semibold" for="bulkFieldVia">Via Pembelian</label></div>
                            <input type="text" name="via" id="bulkVia" class="form-control" placeholder="Contoh: Tokopedia" disabled>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="nama_alamat" data-target="bulkNamaAlamat" id="bulkFieldAlamat"><label class="form-check-label fw-semibold" for="bulkFieldAlamat">Nama / Alamat</label></div>
                            <input type="text" name="nama_alamat" id="bulkNamaAlamat" class="form-control" placeholder="Nama atau alamat baru" disabled>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="tanggal_beli" data-target="bulkTanggalBeli" id="bulkFieldTanggal"><label class="form-check-label fw-semibold" for="bulkFieldTanggal">Tanggal Beli</label></div>
                            <input type="date" name="tanggal_beli" id="bulkTanggalBeli" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-1"><input class="form-check-input bulk-field-toggle" type="checkbox" name="fields[]" value="total_modal" data-target="bulkTotalModal" id="bulkFieldModal"><label class="form-check-label fw-semibold" for="bulkFieldModal">Total Modal</label></div>
                            <input type="number" name="total_modal" id="bulkTotalModal" class="form-control" min="0" step="1" placeholder="Rp" disabled>
                            <small class="text-muted">Untuk Titipan tanpa untung, modal tiap barang mengikuti harga master.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="bi bi-save me-1"></i>Terapkan ke Barang Terpilih</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Independen per Item -->
@foreach($pembelians as $item)
<form id="formDestroySingle{{ $item->id }}" action="{{ route('pembelian.destroy', $item->id) }}" method="POST" class="d-none" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data rekap pembelian ini?')">
    @csrf
    @method('DELETE')
</form>
@endforeach

<!-- Modal Edit Pembelian per Item -->
@foreach($pembelians as $item)
<div class="modal fade modal-edit-item" id="modalEditPembelian{{ $item->id }}" data-item-id="{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title fw-bold fs-6">Edit Rekap Pembelian ({{ $item->kode_otomatis }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" data-scanner-action="stop" data-item-id="{{ $item->id }}"></button>
            </div>
            <form action="{{ route('pembelian.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="form-pembelian purchase-titipan-form">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Transaksi Manual (Opsional)</label>
                            <input type="text" name="kode_manual" class="form-control" value="{{ old('kode_manual', $item->kode_manual) }}" placeholder="No. Invoice / Resi Toko">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Alamat (Opsional)</label>
                            <input type="text" name="nama_alamat" class="form-control" value="{{ old('nama_alamat', $item->nama_alamat) }}" placeholder="Keterangan alamat / gudang...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Barang Pembelian</label>
                            <select name="nama_barang" class="form-select" required>
                                <option value="" disabled>-- Pilih Barang --</option>
                                @foreach($barangs as $brg)
                                <option value="{{ $brg->nama_barang }}" data-harga="{{ (int) $brg->harga_jual }}" {{ $item->nama_barang == $brg->nama_barang ? 'selected' : '' }}>
                                    {{ $brg->nama_barang }}
                                </option>
                                @endforeach
                            </select>
                        </div>



                        <div class="col-md-6">
                            <label class="form-label">Nama Toko Pembelian</label>
                            <select name="nama_toko" class="form-select" required>
                                <option value="" disabled>-- Pilih Toko --</option>
                                @foreach($tokos as $tk)
                                <option value="{{ $tk->nama_toko }}" {{ $item->nama_toko == $tk->nama_toko ? 'selected' : '' }}>
                                    {{ $tk->nama_toko }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                        $isCustomVia = !in_array($item->via, ['Tokopedia', 'Shopee', 'Lazada', 'TikTok']);
                        @endphp
                        <div class="col-md-6">
                            <label class="form-label">Transaksi Beli Via</label>
                            <select id="selectViaEdit{{ $item->id }}" class="form-select select-via-toggle" data-target="#inputViaEdit{{ $item->id }}" required>
                                <option value="Tokopedia" {{ $item->via == 'Tokopedia' ? 'selected' : '' }}>Tokopedia</option>
                                <option value="Shopee" {{ $item->via == 'Shopee' ? 'selected' : '' }}>Shopee</option>
                                <option value="Lazada" {{ $item->via == 'Lazada' ? 'selected' : '' }}>Lazada</option>
                                <option value="TikTok" {{ $item->via == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                <option value="COD" {{ $item->via == 'COD' ? 'selected' : '' }}>COD</option>
                                <option value="Lainnya" {{ $isCustomVia ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                            </select>
                            <input
                                type="text"
                                id="inputViaEdit{{ $item->id }}"
                                name="via"
                                class="form-control mt-2 {{ $isCustomVia ? '' : 'd-none' }}"
                                value="{{ $item->via }}"
                                placeholder="Ketik platform/via transaksi manual..."
                                {{ $isCustomVia ? 'required' : '' }}>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Trader</label>
                            <input type="text" name="nama_trader" class="form-control" value="{{ old('nama_trader', $item->nama_trader) }}" placeholder="Masukkan nama trader (opsional)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Titipan</label>
                            <select name="titipan" class="form-select titipan-select" required>
                                @foreach(['Tidak', 'Ya', 'Ya, (tidak ambil untung)'] as $titipanOption)
                                <option value="{{ $titipanOption }}" {{ ($item->titipan ?? 'Tidak') === $titipanOption ? 'selected' : '' }}>{{ $titipanOption }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tanggal Beli</label>
                            <input type="date" name="tanggal_beli" class="form-control" value="{{ old('tanggal_beli', $item->tanggal_beli) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Total Modal (Rp)</label>
                            <input
                                type="text"
                                name="total_modal"
                                class="form-control input-rupiah total-modal-titipan"
                                value="{{ number_format($item->total_modal, 0, ',', '.') }}"
                                placeholder="Misal: 8.500.000"
                                required
                                autocomplete="off">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status Barang</label>
                            <select name="status" class="form-select" required>
                                <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                                <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                                <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                                <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                                <option value="Jual" {{ $item->status == 'Jual' ? 'selected' : '' }}>🏷️ Jual</option>
                                <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>🏁 Selesai</option>
                            </select>
                        </div>

                        <div class="col-12 bg-light p-3 rounded border">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-barcode me-1"></i> Nomor IMEI / Detail IMEI</label>
                            <div class="input-group">
                                <input type="text" id="imeiInput{{ $item->id }}" name="detail_imei" class="form-control font-monospace" value="{{ old('detail_imei', $item->detail_imei) }}" placeholder="Ketik manual atau scan otomatis kamera...">
                                <button type="button" class="btn btn-outline-primary fw-semibold" data-scanner-action="start" data-item-id="{{ $item->id }}">
                                    <i class="bi bi-camera"></i> Scan
                                </button>
                            </div>

                            <div id="readerWrapper{{ $item->id }}" class="mt-2 d-none text-center">
                                <div class="alert alert-info py-2 small mb-2">
                                    <i class="bi bi-info-circle"></i> Arahkan kamera ke barcode/QR Code IMEI pada dus HP.
                                </div>
                                <div id="reader{{ $item->id }}" class="border rounded overflow-hidden" style="width: 100%; max-width: 450px; margin: 0 auto; min-height: 220px; background-color: #000;"></div>
                                <button type="button" class="btn btn-sm btn-secondary mt-2 px-3" data-scanner-action="stop" data-item-id="{{ $item->id }}">Tutup Kamera</button>
                            </div>
                        </div>

                        @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                        <div class="col-12">
                            <label class="form-label fw-semibold">Lampiran Ter-upload (Centang untuk hapus):</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($item->file_lampiran as $idx => $filePath)
                                <div class="border rounded p-2 bg-light d-flex flex-column align-items-start gap-1" style="min-width: 110px;">
                                    <span class="small fw-semibold text-truncate w-100 text-muted">
                                        <i class="bi bi-paperclip"></i> File {{ $idx + 1 }}
                                    </span>
                                    <div class="form-check form-check-inline m-0 pt-1 border-top w-100">
                                        <input class="form-check-input bg-danger border-danger" type="checkbox" name="delete_files[]" value="{{ $idx }}" id="delFile{{ $item->id }}_{{ $idx }}">
                                        <label class="form-check-label small text-danger fw-semibold" for="delFile{{ $item->id }}_{{ $idx }}" style="font-size: 0.75rem;">
                                            Hapus
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label">Tambah Lampiran Baru</label>
                            <input type="file" name="file_lampiran[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                            <small class="text-muted fs-7">Bisa pilih lebih dari 1 file (JPG, PNG, PDF maks 2MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-scanner-action="stop" data-item-id="{{ $item->id }}">Batal</button>
                    <button type="submit" class="btn btn-warning text-white fw-semibold">Update Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Modal Preview File / Gambar Interaktif -->
<div class="modal fade" id="modalFilePreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white shadow-lg border-0">
            <div class="modal-header border-secondary py-2">
                <h5 class="modal-title fs-6 fw-semibold text-truncate" id="previewModalTitle">Preview Lampiran</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnZoomIn" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnZoomOut" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnResetZoom" title="Reset Ukuran"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <a href="#" id="btnDownloadFile" target="_blank" class="btn btn-sm btn-primary" title="Download Asli"><i class="bi bi-download"></i></a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative overflow-hidden d-flex justify-content-center align-items-center" style="height: 70vh; background-color: #121212;">
                <!-- Kontainer Gambar dengan Panzoom -->
                <div id="imagePreviewContainer" class="w-100 h-100 d-flex justify-content-center align-items-center position-relative">
                    <img id="previewImageElement" src="" alt="Preview Lampiran" class="d-none" style="max-height: 100%; max-width: 100%; object-fit: contain; cursor: grab;" />
                </div>
                <!-- Kontainer PDF Viewer -->
                <div id="pdfPreviewContainer" class="w-100 h-100 d-none">
                    <iframe id="previewPdfElement" src="" class="w-100 h-100 border-0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TERPISAH 1: Rekap Total Kuantitas Barang per Toko -->
<div class="modal fade" id="modalTotalBarangPerToko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary fs-6"><i class="bi bi-box-seam-fill me-2"></i>Rekap Kuantitas Barang per Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                // Kelompokkan data berdasarkan Toko (COD / Nama Toko)
                $groupedTokoForRekap = $pembelians->groupBy(function($item) {
                return strtolower($item->via ?? '') === 'cod' ? 'COD' : ($item->nama_toko ?: 'Lainnya');
                });

                // Rekap Per Toko
                $plainTextRekapBarang = "";
                foreach($groupedTokoForRekap as $tokoName => $itemsInToko) {
                $plainTextRekapBarang .= $tokoName . "\n";
                $rekapBarang = $itemsInToko->groupBy(function($item) {
                return trim($item->nama_device ?: $item->nama_barang);
                })->map->count();

                foreach($rekapBarang as $namaBarang => $totalUnit) {
                $plainTextRekapBarang .= "• " . $namaBarang . " : " . $totalUnit . " Unit\n";
                }
                $plainTextRekapBarang .= "\n";
                }

                // Rekap TOTAL UNIT Keseluruhan (Gabungan Semua Toko)
                $rekapTotalUnitSemua = $pembelians->groupBy(function($item) {
                return trim($item->nama_device ?: $item->nama_barang);
                })->map->count();

                if($rekapTotalUnitSemua->count() > 0) {
                $plainTextRekapBarang .= "TOTAL UNIT\n";
                foreach($rekapTotalUnitSemua as $namaBarang => $totalUnit) {
                $plainTextRekapBarang .= "• " . $namaBarang . " : " . $totalUnit . " Unit\n";
                }
                }

                $plainTextRekapBarang = trim($plainTextRekapBarang);
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Daftar teks murni rekap barang:</span>
                    <span class="badge bg-primary fs-6 px-3 py-1.5">Total: {{ $pembelians->count() }} Unit</span>
                </div>

                <!-- Textarea Teks Murni Rekap Kuantitas Barang -->
                <textarea id="textRekapArea" class="form-control font-monospace border bg-light" rows="12" style="font-size: 0.85rem;" readonly>{{ $plainTextRekapBarang }}</textarea>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnSalinRekapBarang">
                    <i class="bi bi-clipboard me-1"></i> Salin Rekap
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TERPISAH 2: Salin Ringkasan Pesanan Checklist -->
<div class="modal fade" id="modalRingkasanSalin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-clipboard-check me-2"></i>Ringkasan Pesanan per Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('pembelian.saveChecklist') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-2 text-muted small">Centang pesanan lalu simpan checklist agar tersimpan:</div>

                    @php
                    $groupedByToko = $pembelians->groupBy(function($item) {
                    return strtolower($item->via ?? '') === 'cod' ? 'COD' : ($item->nama_toko ?: 'Lainnya');
                    });
                    @endphp

                    <div id="containerRingkasanChecklist" class="border rounded p-3 bg-light font-monospace overflow-auto" style="max-height: 350px; font-size: 0.82rem;">
                        @foreach($groupedByToko as $tokoName => $itemsGroup)
                        <div class="fw-bold text-dark mb-1 toko-title-heading" data-toko="{{ $tokoName }}">{{ $tokoName }}</div>
                        @foreach($itemsGroup as $idx => $item)
                        @php
                        $labelTeksPesanan = ($idx + 1) . '. ' . ($item->kode_manual ?? '-') . ' - ' . ($item->nama_alamat ?? '-') . ' - ' . trim(($item->nama_device ?: $item->nama_barang));
                        @endphp
                        <div class="form-check mb-1 item-checklist-wrapper">
                            <input class="form-check-input ringkasan-checkbox"
                                type="checkbox"
                                name="checked_ids[]"
                                value="{{ $item->id }}"
                                {{ $item->is_checked ? 'checked' : '' }}
                                id="checkRingkasan{{ $item->id }}">
                            <label class="form-check-label text-dark" for="checkRingkasan{{ $item->id }}" data-raw-text="{{ $labelTeksPesanan }}">
                                {{ $labelTeksPesanan }}
                            </label>
                        </div>
                        @endforeach
                        <div class="mb-2"></div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm fw-bold px-3" id="btnSalinClipboard">
                            <i class="bi bi-clipboard me-1"></i> Salin Clipboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TERPISAH 3: Pengecekan IMEI Duplikat di Rekap Pembelian -->
<div class="modal fade" id="modalCekDuplikatImeiIndex" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-shield-exclamation me-2"></i> Laporan IMEI Duplikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Status Alert Hasil Cek -->
                <div id="alertSummaryIndexDuplikat" class="alert d-flex align-items-center mb-3" role="alert">
                    <i id="iconSummaryIndexDuplikat" class="fs-3 me-3"></i>
                    <div>
                        <strong id="titleSummaryIndexDuplikat"></strong>
                        <div id="descSummaryIndexDuplikat" class="small"></div>
                    </div>
                </div>

                <!-- Kontainer Rincian Data Duplikat -->
                <div id="containerRincianIndexDuplikat" class="d-none">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-list-task me-1"></i> IMEI Double:</h6>
                    <div class="table-responsive border rounded" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-striped align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;" class="text-center">No</th>
                                    <th>Nomor IMEI / Serial</th>
                                    <th class="text-center" style="width: 90px;">Jumlah</th>
                                    <th>Item Terkait</th>
                                </tr>
                            </thead>
                            <tbody id="bodyTabelIndexDuplikat">
                                <!-- Terisi via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    let activeScanners = {};
    let panzoomInstance = null;

    // Fungsi Pengecekan IMEI Duplikat pada Rekap Pembelian
    function jalankanCekDuplikatImeiIndex() {
        const isMobile = window.innerWidth < 768;
        const targetSelector = isMobile ? '.row-pembelian-mobile' : '.row-pembelian-desktop';
        const rows = document.querySelectorAll(targetSelector);

        let mapImei = {};

        rows.forEach(row => {
            const rawImei = row.getAttribute('data-detail-imei');
            const barang = row.getAttribute('data-nama-barang');
            const kodeOtomatis = row.getAttribute('data-kode-otomatis');
            const kodeManual = row.getAttribute('data-kode-manual');
            const toko = row.getAttribute('data-nama-toko');
            const alamat = row.getAttribute('data-nama-alamat');
            const tglBeli = row.getAttribute('data-tanggal-beli');
            const status = row.getAttribute('data-status');

            if (rawImei && rawImei !== '-' && rawImei !== '') {
                const listImei = rawImei.split(/[\n,]+/).map(s => s.trim()).filter(s => s !== '');
                listImei.forEach(imei => {
                    if (imei && imei !== '-') {
                        if (!mapImei[imei]) mapImei[imei] = [];
                        mapImei[imei].push({
                            barang,
                            kodeOtomatis,
                            kodeManual,
                            toko,
                            alamat,
                            tglBeli,
                            status
                        });
                    }
                });
            }
        });

        let listDuplikatImei = [];
        for (let imei in mapImei) {
            if (mapImei[imei].length > 1) {
                listDuplikatImei.push({
                    val: imei,
                    count: mapImei[imei].length,
                    items: mapImei[imei]
                });
            }
        }

        const alertBox = document.getElementById('alertSummaryIndexDuplikat');
        const iconBox = document.getElementById('iconSummaryIndexDuplikat');
        const titleBox = document.getElementById('titleSummaryIndexDuplikat');
        const descBox = document.getElementById('descSummaryIndexDuplikat');
        const containerRincian = document.getElementById('containerRincianIndexDuplikat');
        const bodyTabel = document.getElementById('bodyTabelIndexDuplikat');

        bodyTabel.innerHTML = '';

        if (listDuplikatImei.length === 0) {
            alertBox.className = 'alert alert-success d-flex align-items-center mb-3';
            iconBox.className = 'bi bi-check-circle-fill fs-3 me-3 text-success';
            titleBox.innerText = 'Data IMEI Bersih!';
            descBox.innerText = 'Tidak ditemukan IMEI duplikat.';
            containerRincian.classList.add('d-none');
        } else {
            alertBox.className = 'alert alert-danger d-flex align-items-center mb-3';
            iconBox.className = 'bi bi-exclamation-triangle-fill fs-3 me-3 text-danger';
            titleBox.innerText = `Ditemukan ${listDuplikatImei.length} IMEI Duplikat!`;
            descBox.innerText = 'Silakan periksa nomor IMEI yang terinput ganda:';
            containerRincian.classList.remove('d-none');

            listDuplikatImei.forEach((item, index) => {
                let detailItemsHtml = item.items.map(it => `
                    <div class="mb-1 pb-1 border-bottom border-light-subtle">
                        <strong>${it.kodeOtomatis}</strong> (${it.kodeManual}) - <strong>${it.barang}</strong>
                        <br>
                        <small class="text-muted">
                            ${it.toko} | Tgl Beli: <strong>${it.tglBeli}</strong>
                        </small>
                    </div>
                `).join('');

                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center text-muted fw-semibold align-middle">${index + 1}</td>
                    <td class="fw-bold font-monospace text-danger align-middle">${item.val}</td>
                    <td class="text-center align-middle"><span class="badge bg-danger">${item.count}x</span></td>
                    <td class="text-dark">${detailItemsHtml}</td>
                `;
                bodyTabel.appendChild(tr);
            });
        }
    }

    // Helper Universal Salin Teks Murni ke Clipboard
    function copyTextDirectly(text, btnEl, defaultBtnHtml) {
        if (!text || !text.trim()) {
            alert("Tidak ada teks untuk disalin.");
            return;
        }

        function showSuccessState() {
            btnEl.innerHTML = '<i class="bi bi-check-lg me-1"></i> Disalin!';
            btnEl.classList.remove('btn-primary');
            btnEl.classList.add('btn-success');
            setTimeout(() => {
                btnEl.innerHTML = defaultBtnHtml;
                btnEl.classList.remove('btn-success');
                btnEl.classList.add('btn-primary');
            }, 2000);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(showSuccessState).catch(() => {
                fallbackCopy(text, showSuccessState);
            });
        } else {
            fallbackCopy(text, showSuccessState);
        }
    }

    function fallbackCopy(text, callback) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.opacity = "0";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            const successful = document.execCommand('copy');
            if (successful && callback) callback();
            else alert('Gagal menyalin teks.');
        } catch (err) {
            alert('Gagal menyalin teks: ' + err);
        }
        document.body.removeChild(textArea);
    }

    function startScanner(id) {
        const wrapper = document.getElementById(`readerWrapper${id}`);
        if (!wrapper) return;
        wrapper.classList.remove('d-none');

        if (activeScanners[id]) return;

        const html5QrCode = new Html5Qrcode(`reader${id}`);
        activeScanners[id] = html5QrCode;

        const config = {
            fps: 15,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                return {
                    width: Math.floor(viewfinderWidth * 0.8),
                    height: Math.floor(viewfinderHeight * 0.5)
                };
            },
            aspectRatio: 1.0,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        html5QrCode.start({
                facingMode: "environment"
            },
            config,
            (decodedText, decodedResult) => {
                const imeiInput = document.getElementById(`imeiInput${id}`);
                if (imeiInput) imeiInput.value = decodedText;

                if (navigator.vibrate) navigator.vibrate(100);

                stopScanner(id);
            },
            (errorMessage) => {}
        ).catch(err => {
            alert("Gagal mengakses kamera: " + err);
            wrapper.classList.add('d-none');
            delete activeScanners[id];
        });
    }

    async function stopScanner(id) {
        const wrapper = document.getElementById(`readerWrapper${id}`);

        if (activeScanners[id]) {
            try {
                if (activeScanners[id].isScanning) {
                    await activeScanners[id].stop();
                }
            } catch (err) {
                console.warn("Kamera dihentikan:", err);
            } finally {
                if (wrapper) wrapper.classList.add('d-none');
                delete activeScanners[id];
            }
        } else {
            if (wrapper) wrapper.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(event) {
            const scannerButton = event.target.closest('[data-scanner-action]');
            if (!scannerButton) return;

            const scannerId = scannerButton.dataset.itemId;
            if (scannerButton.dataset.scannerAction === 'start') {
                startScanner(scannerId);
            } else {
                stopScanner(scannerId);
            }
        });

        // Panzoom Init
        const previewImage = document.getElementById('previewImageElement');
        if (previewImage) {
            panzoomInstance = Panzoom(previewImage, {
                maxScale: 5,
                minScale: 0.5,
                contain: 'outside',
                startScale: 1,
                cursor: 'grab'
            });

            previewImage.parentElement.addEventListener('wheel', panzoomInstance.zoomWithWheel);

            document.getElementById('btnZoomIn').addEventListener('click', () => panzoomInstance.zoomIn());
            document.getElementById('btnZoomOut').addEventListener('click', () => panzoomInstance.zoomOut());
            document.getElementById('btnResetZoom').addEventListener('click', () => {
                panzoomInstance.reset();
            });
        }

        // File Preview
        document.querySelectorAll('.preview-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                const ext = this.getAttribute('data-ext');
                const title = this.getAttribute('data-title');

                document.getElementById('previewModalTitle').textContent = title;
                document.getElementById('btnDownloadFile').setAttribute('href', url);

                const imgContainer = document.getElementById('imagePreviewContainer');
                const pdfContainer = document.getElementById('pdfPreviewContainer');
                const imgElement = document.getElementById('previewImageElement');
                const pdfElement = document.getElementById('previewPdfElement');

                if (ext === 'pdf') {
                    imgContainer.classList.add('d-none');
                    imgElement.classList.add('d-none');
                    pdfContainer.classList.remove('d-none');
                    pdfElement.setAttribute('src', url);
                } else {
                    pdfContainer.classList.add('d-none');
                    pdfElement.setAttribute('src', '');
                    imgContainer.classList.remove('d-none');
                    imgElement.classList.remove('d-none');

                    imgElement.setAttribute('src', url);
                    if (panzoomInstance) panzoomInstance.reset();
                }

                const previewModal = new bootstrap.Modal(document.getElementById('modalFilePreview'));
                previewModal.show();
            });
        });

        // 1. KLIK TOMBOL: Salin Rekap Total Barang
        const btnSalinRekapBarang = document.getElementById('btnSalinRekapBarang');
        const textRekapArea = document.getElementById('textRekapArea');

        if (btnSalinRekapBarang && textRekapArea) {
            btnSalinRekapBarang.addEventListener('click', function(e) {
                e.preventDefault();
                copyTextDirectly(textRekapArea.value, btnSalinRekapBarang, '<i class="bi bi-clipboard me-1"></i> Salin Rekap');
            });
        }

        // 2. KLIK TOMBOL: Salin Ringkasan Pesanan Checklist
        const btnSalinClipboard = document.getElementById('btnSalinClipboard');
        const containerRingkasan = document.getElementById('containerRingkasanChecklist');

        if (btnSalinClipboard && containerRingkasan) {
            btnSalinClipboard.addEventListener('click', function(e) {
                e.preventDefault();
                let lines = [];
                containerRingkasan.querySelectorAll('.toko-title-heading, .item-checklist-wrapper').forEach(node => {
                    if (node.classList.contains('toko-title-heading')) {
                        lines.push(node.getAttribute('data-toko'));
                    } else if (node.classList.contains('item-checklist-wrapper')) {
                        let checkbox = node.querySelector('.ringkasan-checkbox');
                        let labelEl = node.querySelector('label');
                        if (checkbox && labelEl) {
                            let mark = checkbox.checked ? "[✔]" : "[ ]";
                            lines.push(`${mark} ${labelEl.getAttribute('data-raw-text')}`);
                        }
                    }
                });

                const textToCopy = lines.join('\n').trim();
                copyTextDirectly(textToCopy, btnSalinClipboard, '<i class="bi bi-clipboard me-1"></i> Salin Clipboard');
            });
        }

        // Checkbox Bulk Status Massal (Perbaikan Akurat Tanpa Duplikasi)
        const selectAllCheckbox = document.getElementById('selectAll');
        const selectAllMobileCheckbox = document.getElementById('selectAllMobile');
        const bulkActionBar = document.getElementById('bulkActionBar');
        const selectedCountSpan = document.getElementById('selectedCount');

        function updateBulkBar() {
            const checkedDesktop = document.querySelectorAll('.item-checkbox-desktop:checked');
            const checkedMobile = document.querySelectorAll('.item-checkbox-mobile:checked');

            // Mengambil daftar ID unik yang tercentang
            let uniqueSelectedIds = new Set();
            checkedDesktop.forEach(cb => uniqueSelectedIds.add(cb.getAttribute('data-id')));
            checkedMobile.forEach(cb => uniqueSelectedIds.add(cb.getAttribute('data-id')));

            const totalUnique = uniqueSelectedIds.size;
            if (totalUnique > 0) {
                bulkActionBar.classList.remove('d-none');
                selectedCountSpan.textContent = totalUnique;
            } else {
                bulkActionBar.classList.add('d-none');
            }
        }

        function toggleAllCheckboxes(status) {
            const desktopCbs = document.querySelectorAll('.item-checkbox-desktop');
            const mobileCbs = document.querySelectorAll('.item-checkbox-mobile');

            desktopCbs.forEach(cb => cb.checked = status);
            mobileCbs.forEach(cb => cb.checked = status);

            if (selectAllCheckbox) selectAllCheckbox.checked = status;
            if (selectAllMobileCheckbox) selectAllMobileCheckbox.checked = status;

            updateBulkBar();
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                toggleAllCheckboxes(this.checked);
            });
        }

        if (selectAllMobileCheckbox) {
            selectAllMobileCheckbox.addEventListener('change', function() {
                toggleAllCheckboxes(this.checked);
            });
        }

        // Sinkronisasi Checklist Desktop & Mobile
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                const itemId = this.getAttribute('data-id');
                const isChecked = this.checked;

                const desktopCb = document.querySelector(`.item-checkbox-desktop[data-id="${itemId}"]`);
                const mobileCb = document.querySelector(`.item-checkbox-mobile[data-id="${itemId}"]`);

                if (desktopCb) desktopCb.checked = isChecked;
                if (mobileCb) mobileCb.checked = isChecked;

                updateBulkBar();

                const totalDesktop = document.querySelectorAll('.item-checkbox-desktop').length;
                const totalCheckedDesktop = document.querySelectorAll('.item-checkbox-desktop:checked').length;
                const isAllChecked = (totalCheckedDesktop === totalDesktop) && (totalDesktop > 0);

                if (selectAllCheckbox) selectAllCheckbox.checked = isAllChecked;
                if (selectAllMobileCheckbox) selectAllMobileCheckbox.checked = isAllChecked;
            });
        });

        // Search Input Delay Submit
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timer;

        if (searchInput && searchForm) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') return;
                clearTimeout(timer);
                timer = setTimeout(() => {
                    searchForm.submit();
                }, 700);
            });
        }

        function syncTitipanPrice(form) {
            const titipan = form.querySelector('.titipan-select');
            const nameInput = form.querySelector('[name="nama_barang"]');
            const totalInput = form.querySelector('.total-modal-titipan');
            if (!titipan || !nameInput || !totalInput) return;

            const useMasterPrice = titipan.value === 'Ya, (tidak ambil untung)';
            totalInput.readOnly = useMasterPrice;
            if (!useMasterPrice) return;

            const price = nameInput.selectedOptions?.[0]?.dataset.harga;
            totalInput.value = price === undefined ? '' : Math.round(Number(price)).toLocaleString('id-ID');
        }

        document.querySelectorAll('.purchase-titipan-form').forEach(function(form) {
            form.querySelector('.titipan-select')?.addEventListener('change', function() {
                syncTitipanPrice(form);
            });
            form.querySelector('[name="nama_barang"]')?.addEventListener('change', function() {
                syncTitipanPrice(form);
            });
            syncTitipanPrice(form);
        });

        const modalEditMassal = document.getElementById('modalEditMassal');
        const selectedIdsContainer = document.getElementById('editMassalSelectedIds');
        modalEditMassal?.addEventListener('show.bs.modal', function() {
            const selectedIds = new Set(
                Array.from(document.querySelectorAll('#formMassal .item-checkbox:checked'))
                .map(checkbox => checkbox.dataset.id)
                .filter(Boolean)
            );
            selectedIdsContainer.replaceChildren();
            selectedIds.forEach(function(id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'pembelian_ids[]';
                input.value = id;
                selectedIdsContainer.appendChild(input);
            });
            document.getElementById('editMassalSelectedCount').textContent = selectedIds.size;
        });

        document.querySelectorAll('.bulk-field-toggle').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const input = document.getElementById(this.dataset.target);
                if (input) input.disabled = !this.checked;
            });
        });

        document.getElementById('formEditMassal')?.addEventListener('submit', function(event) {
            if (!selectedIdsContainer.querySelector('input[name="pembelian_ids[]"]')) {
                event.preventDefault();
                alert('Pilih minimal satu barang terlebih dahulu.');
                return;
            }
            if (!this.querySelector('.bulk-field-toggle:checked')) {
                event.preventDefault();
                alert('Pilih minimal satu field yang ingin diubah.');
            }
        });

        // Select Via Toggle Edit Modal
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('select-via-toggle')) {
                const selectElement = e.target;
                const targetInput = document.querySelector(selectElement.dataset.target);

                if (targetInput) {
                    if (selectElement.value === 'Lainnya') {
                        targetInput.classList.remove('d-none');
                        targetInput.value = '';
                        targetInput.focus();
                        targetInput.required = true;
                    } else {
                        targetInput.classList.add('d-none');
                        targetInput.value = selectElement.value;
                        targetInput.required = false;
                    }
                }
            }
        });

        // Rupiah Format
        function formatRupiah(angka) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        document.addEventListener('keyup', function(e) {
            if (e.target && e.target.classList.contains('input-rupiah')) {
                e.target.value = formatRupiah(e.target.value);
            }
        });

        document.querySelectorAll('.form-pembelian').forEach(function(form) {
            form.addEventListener('submit', function() {
                let rupiahInput = form.querySelector('.input-rupiah');
                if (rupiahInput) {
                    rupiahInput.value = rupiahInput.value.replace(/\./g, '');
                }
            });
        });
    });
</script>
@endsection