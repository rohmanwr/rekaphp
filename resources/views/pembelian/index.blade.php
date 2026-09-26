@extends('layouts.app')

@section('title', 'Rekap Pembelian HP')

@section('content')
<!-- Library Scanner Barcode HTML5 -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<!-- Library Panzoom & Hammer.js untuk fitur Zoom dan Geser Gambar -->
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Rekap Pembelian</h3>
    <div class="d-flex align-items-center gap-2">
        <!-- Tombol Terpisah 1: Rekap Total Kuantitas Barang per Toko -->
        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTotalBarangPerToko" title="Lihat Total Kuantitas Barang per Toko">
            <i class="bi bi-box-seam-fill me-1"></i> Total Barang
        </button>

        <!-- Tombol Terpisah 2: Ringkasan Salin Pesanan Checklist -->
        <button type="button" class="btn btn-outline-dark btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalRingkasanSalin" title="Salin Ringkasan Pesanan">
            <i class="bi bi-clipboard-check me-1"></i> Salin Ringkasan
        </button>

        <a href="{{ route('pembelian.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Pembelian
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
    <div class="card-body">
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
                    placeholder="Cari otomatis berdasarkan Kode TRX, Nama Barang, Device, Toko, atau IMEI..."
                    value="{{ $search }}"
                    autocomplete="off"
                    autofocus>
                @if(!empty($search))
                <a href="{{ route('pembelian.index', ['status' => $selectedStatus]) }}" class="btn btn-outline-secondary" title="Reset Pencarian Teks">
                    <i class="bi bi-x-lg"></i> Reset
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
        @endphp

        <!-- Filter Tombol Status Beserta Jumlah -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold text-muted small me-1"><i class="bi bi-funnel-fill"></i> Filter Status:</span>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search])) }}"
                class="btn btn-sm rounded-pill {{ empty($selectedStatus) ? 'btn-dark' : 'btn-outline-secondary' }}">
                Semua <span class="badge bg-light text-dark ms-1">{{ $countSemua }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Belum Ready'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Belum Ready' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">
                ⏳ Belum Ready <span class="badge bg-dark text-white ms-1">{{ $countBelumReady }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Ready'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Sudah Ready' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                ✅ Sudah Ready <span class="badge bg-light text-dark ms-1">{{ $countSudahReady }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Diambil'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Sudah Diambil' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                📦 Sudah Diambil <span class="badge bg-light text-dark ms-1">{{ $countSudahDiambil }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Bermasalah'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Bermasalah' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                ⚠️ Bermasalah <span class="badge bg-light text-dark ms-1">{{ $countBermasalah }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Jual'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Jual' ? 'btn-info text-white fw-bold' : 'btn-outline-info text-dark' }}">
                🏷️ Jual <span class="badge bg-light text-dark ms-1">{{ $countJual }}</span>
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Selesai'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Selesai' ? 'btn-secondary text-white fw-bold' : 'btn-outline-secondary' }}">
                🏁 Selesai <span class="badge bg-light text-dark ms-1">{{ $countSelesai }}</span>
            </a>
        </div>
    </div>
</div>

<!-- Form Update Status Massal -->
<form action="{{ route('pembelian.updateStatusMassal') }}" method="POST" id="formMassal">
    @csrf
    @method('PATCH')

    <!-- Tabel Rekap Pembelian -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="form-check-input" title="Pilih Semua">
                            </th>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Kode Manual / Alamat</th>
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
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="pembelian_ids[]" value="{{ $item->id }}" class="form-check-input item-checkbox">
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
                            </td>
                            <td>
                                <strong>{{ $item->nama_barang }}</strong>
                                @if(!empty($item->nama_device))
                                <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $item->nama_device }}</small>
                                @endif
                                <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
                            </td>
                            <td>
                                @if(!empty($item->detail_imei))
                                <span class="badge bg-light text-dark border font-monospace">{{ $item->detail_imei }}</span>
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
                                <!-- Quick Update Status Langsung via Form PATCH -->
                                <form action="{{ route('pembelian.updateStatus', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm rounded-pill fw-semibold" onchange="this.form.submit()" style="width: fit-content; min-width: 140px;">
                                        <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                                        <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                                        <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                                        <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                                        <option value="Jual" {{ $item->status == 'Jual' ? 'selected' : '' }}>🏷️ Jual</option>
                                        <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>🏁 Selesai</option>
                                    </select>
                                </form>
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

                                    <form action="{{ route('pembelian.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data rekap pembelian ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger fw-semibold" title="Hapus Transaksi">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Pembelian -->
                        <div class="modal fade modal-edit-item" id="modalEditPembelian{{ $item->id }}" data-item-id="{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content text-start">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Edit Rekap Pembelian ({{ $item->kode_otomatis }})</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopScanner({{ $item->id }})"></button>
                                    </div>
                                    <form action="{{ route('pembelian.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="form-pembelian">
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
                                                        <option value="{{ $brg->nama_barang }}" {{ $item->nama_barang == $brg->nama_barang ? 'selected' : '' }}>
                                                            {{ $brg->nama_barang }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Nama Device (Spesifik)</label>
                                                    <select name="nama_device" class="form-select">
                                                        <option value="" selected>-- Pilih Device --</option>
                                                        @foreach($devices as $dev)
                                                        <option value="{{ $dev->nama_device }}" {{ $item->nama_device == $dev->nama_device ? 'selected' : '' }}>
                                                            [{{ $dev->kode_device }}] {{ $dev->nama_device }}
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
                                                    <label class="form-label">Tanggal Beli</label>
                                                    <input type="date" name="tanggal_beli" class="form-control" value="{{ old('tanggal_beli', $item->tanggal_beli) }}" required>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Total Modal (Rp)</label>
                                                    <input
                                                        type="text"
                                                        name="total_modal"
                                                        class="form-control input-rupiah"
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
                                                        <button type="button" class="btn btn-outline-primary fw-semibold" onclick="startScanner({{ $item->id }})">
                                                            <i class="bi bi-camera"></i> Auto Scan Barcode
                                                        </button>
                                                    </div>

                                                    <div id="readerWrapper{{ $item->id }}" class="mt-2 d-none text-center">
                                                        <div class="alert alert-info py-2 small mb-2">
                                                            <i class="bi bi-info-circle"></i> Arahkan kamera ke barcode/QR Code IMEI pada dus HP. Barcode akan otomatis terdeteksi.
                                                        </div>
                                                        <div id="reader{{ $item->id }}" class="border rounded overflow-hidden" style="width: 100%; max-width: 450px; margin: 0 auto; min-height: 250px; background-color: #000;"></div>
                                                        <button type="button" class="btn btn-sm btn-secondary mt-2 px-3" onclick="stopScanner({{ $item->id }})">Tutup Kamera</button>
                                                    </div>
                                                </div>

                                                @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Lampiran Ter-upload (Centang untuk menghapus saat update):</label>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($item->file_lampiran as $idx => $filePath)
                                                        <div class="border rounded p-2 bg-light d-flex flex-column align-items-start gap-1" style="min-width: 120px;">
                                                            <span class="small fw-semibold text-truncate w-100 text-muted">
                                                                <i class="bi bi-paperclip"></i> File {{ $idx + 1 }}
                                                            </span>
                                                            <div class="form-check form-check-inline m-0 pt-1 border-top w-100">
                                                                <input class="form-check-input bg-danger border-danger" type="checkbox" name="delete_files[]" value="{{ $idx }}" id="delFile{{ $item->id }}_{{ $idx }}">
                                                                <label class="form-check-label small text-danger fw-semibold" for="delFile{{ $item->id }}_{{ $idx }}" style="font-size: 0.75rem;">
                                                                    Hapus File
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="col-md-6">
                                                    <label class="form-label">Tambah Lampiran Baru (Bisa Banyak)</label>
                                                    <input type="file" name="file_lampiran[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                                                    <small class="text-muted fs-7">Bisa pilih lebih dari 1 file (JPG, PNG, PDF maks 2MB/file)</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" onclick="stopScanner({{ $item->id }})">Batal</button>
                                            <button type="submit" class="btn btn-warning text-white fw-semibold">Update Pembelian</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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

    <!-- Floating Action Bar untuk Update Status Massal -->
    <div id="bulkActionBar" class="fixed-bottom bg-dark text-white p-3 shadow-lg d-none" style="z-index: 1050;">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span id="selectedCount" class="fw-bold text-warning">0</span> item dipilih untuk ubah status massal:
            </div>
            <div class="d-flex align-items-center gap-2">
                <select name="status_massal" class="form-select form-select-sm" style="min-width: 180px;">
                    <option value="" disabled selected>-- Pilih Status Baru --</option>
                    <option value="Belum Ready">⏳ Belum Ready</option>
                    <option value="Sudah Ready">✅ Sudah Ready</option>
                    <option value="Sudah Diambil">📦 Sudah Diambil</option>
                    <option value="Bermasalah">⚠️ Bermasalah</option>
                    <option value="Jual">🏷️ Jual</option>
                    <option value="Selesai">🏁 Selesai</option>
                </select>
                <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold px-3">Terapkan Massal</button>
            </div>
        </div>
    </div>
</form>

<!-- Modal Preview File / Gambar Interaktif -->
<div class="modal fade" id="modalFilePreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white shadow-lg border-0">
            <div class="modal-header border-secondary py-2">
                <h5 class="modal-title fs-6 fw-semibold text-truncate" id="previewModalTitle">Preview Lampiran</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnZoomIn" title="Perbesar (Zoom In)"><i class="bi bi-zoom-in"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnZoomOut" title="Perkecil (Zoom Out)"><i class="bi bi-zoom-out"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnResetZoom" title="Reset Ukuran"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                    <a href="#" id="btnDownloadFile" target="_blank" class="btn btn-sm btn-primary" title="Buka/Download Asli"><i class="bi bi-download"></i></a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative overflow-hidden d-flex justify-content-center align-items-center" style="height: 75vh; background-color: #121212;">
                <!-- Kontainer Gambar dengan Panzoom -->
                <div id="imagePreviewContainer" class="w-100 h-100 d-flex justify-content-center align-items-center position-relative">
                    <img id="previewImageElement" src="" alt="Preview Lampiran" class="d-none" style="max-height: 100%; max-width: 100%; object-fit: contain; cursor: grab;" />
                </div>
                <!-- Kontainer PDF Viewer -->
                <div id="pdfPreviewContainer" class="w-100 h-100 d-none">
                    <iframe id="previewPdfElement" src="" class="w-100 h-100 border-0"></iframe>
                </div>
            </div>
            <div class="modal-footer border-secondary py-2 justify-content-center text-muted small">
                <span><i class="bi bi-info-circle me-1"></i> Gunakan *Scroll Mouse* atau tombol Zoom untuk memperbesar, lalu *klik & geser (drag)* untuk menggeser gambar.</span>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TERPISAH 1: Rekap Total Kuantitas Barang per Toko -->
<div class="modal fade" id="modalTotalBarangPerToko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-box-seam-fill me-2"></i>Rekap Kuantitas Barang per Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                // Kelompokkan data berdasarkan Toko (COD / Nama Toko)
                $groupedTokoForRekap = $pembelians->groupBy(function($item) {
                return strtolower($item->via ?? '') === 'cod' ? 'COD' : ($item->nama_toko ?: 'Lainnya');
                });

                // Generate Teks Murni Bersih Langsung via Blade Laravel
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
                $plainTextRekapBarang = trim($plainTextRekapBarang);
                @endphp

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Ringkasan total unit barang dari data yang tampil pada halaman ini.</span>
                    <span class="badge bg-primary fs-6 px-3 py-2">Total: {{ $pembelians->count() }} Unit</span>
                </div>

                <!-- Textarea Teks Murni Interaktif sebagai Kotak Utama Tampilan -->
                <textarea id="textRekapArea" class="form-control font-monospace border bg-light" rows="12" style="font-size: 0.85rem;" readonly>{{ $plainTextRekapBarang }}</textarea>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold px-4" id="btnSalinRekapBarang">
                    <i class="bi bi-clipboard me-1"></i> Salin Rekap Barang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TERPISAH 2: Salin Ringkasan Pesanan Checklist -->
<div class="modal fade" id="modalRingkasanSalin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check me-2"></i>Ringkasan Pesanan per Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('pembelian.saveChecklist') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-2 text-muted small">Centang pesanan lalu klik tombol <strong>Simpan Checklist</strong> agar status centang tersimpan:</div>

                    @php
                    $groupedByToko = $pembelians->groupBy(function($item) {
                    return strtolower($item->via ?? '') === 'cod' ? 'COD' : ($item->nama_toko ?: 'Lainnya');
                    });
                    @endphp

                    <div id="containerRingkasanChecklist" class="border rounded p-3 bg-light font-monospace overflow-auto" style="max-height: 380px; font-size: 0.85rem;">
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

                    <!-- Textarea Tersembunyi Khusus Menampung Hasil Teks Murni Ringkasan -->
                    <textarea id="hiddenRingkasanText" class="d-none"></textarea>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                            <i class="bi bi-save me-1"></i> Simpan Checklist
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary btn-sm fw-bold px-4" id="btnSalinClipboard">
                            <i class="bi bi-clipboard me-1"></i> Salin ke Clipboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let activeScanners = {};
    let panzoomInstance = null;

    // Fungsi Utama Menyalin Teks Murni via DOM Selection (Bekerja 100% di HTTP & HTTPS)
    function executeTextareaCopy(textareaElement, btnElement, defaultBtnHtml) {
        if (!textareaElement) return;

        // Buka temporary jika hidden
        const wasHidden = textareaElement.classList.contains('d-none');
        if (wasHidden) {
            textareaElement.classList.remove('d-none');
            textareaElement.style.position = 'fixed';
            textareaElement.style.left = '-9999px';
            textareaElement.style.top = '-9999px';
        }

        textareaElement.focus();
        textareaElement.select();
        textareaElement.setSelectionRange(0, 99999);

        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (err) {
            copied = false;
        }

        if (wasHidden) {
            textareaElement.classList.add('d-none');
        }

        if (copied) {
            btnElement.innerHTML = '<i class="bi bi-check-lg me-1"></i> Berhasil Disalin!';
            btnElement.classList.remove('btn-primary');
            btnElement.classList.add('btn-success');
            setTimeout(() => {
                btnElement.innerHTML = defaultBtnHtml;
                btnElement.classList.remove('btn-success');
                btnElement.classList.add('btn-primary');
            }, 2000);
        } else {
            alert("Gagal menyalin. Silakan seleksi manual teks tersebut lalu tekan Ctrl+C.");
        }
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

        // Generate Teks Ringkasan Pesanan Checklist
        const containerRingkasan = document.getElementById('containerRingkasanChecklist');
        const hiddenRingkasanText = document.getElementById('hiddenRingkasanText');

        function updateRingkasanDataText() {
            if (!containerRingkasan || !hiddenRingkasanText) return;
            let arrayLines = [];
            containerRingkasan.querySelectorAll('.toko-title-heading, .item-checklist-wrapper').forEach(node => {
                if (node.classList.contains('toko-title-heading')) {
                    arrayLines.push(node.getAttribute('data-toko'));
                } else if (node.classList.contains('item-checklist-wrapper')) {
                    let checkbox = node.querySelector('.ringkasan-checkbox');
                    let labelEl = node.querySelector('label');
                    if (checkbox && labelEl) {
                        let mark = checkbox.checked ? "[✔]" : "[ ]";
                        arrayLines.push(`${mark} ${labelEl.getAttribute('data-raw-text')}`);
                    }
                }
            });
            hiddenRingkasanText.value = arrayLines.join('\n').trim();
        }

        // Inisialisasi awal
        updateRingkasanDataText();
        if (containerRingkasan) {
            containerRingkasan.addEventListener('change', updateRingkasanDataText);
        }

        // 1. EVENT KLIK: Salin Rekap Total Barang
        const btnSalinRekapBarang = document.getElementById('btnSalinRekapBarang');
        const textRekapArea = document.getElementById('textRekapArea');

        if (btnSalinRekapBarang && textRekapArea) {
            btnSalinRekapBarang.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                executeTextareaCopy(textRekapArea, btnSalinRekapBarang, '<i class="bi bi-clipboard me-1"></i> Salin Rekap Barang');
            });
        }

        // 2. EVENT KLIK: Salin Ringkasan Pesanan Checklist
        const btnSalinClipboard = document.getElementById('btnSalinClipboard');
        if (btnSalinClipboard && hiddenRingkasanText) {
            btnSalinClipboard.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                updateRingkasanDataText();
                executeTextareaCopy(hiddenRingkasanText, btnSalinClipboard, '<i class="bi bi-clipboard me-1"></i> Salin ke Clipboard');
            });
        }

        // Checkbox Bulk Status Massal
        const selectAllCheckbox = document.getElementById('selectAll');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const bulkActionBar = document.getElementById('bulkActionBar');
        const selectedCountSpan = document.getElementById('selectedCount');

        function updateBulkBar() {
            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            if (checkedCount > 0) {
                bulkActionBar.classList.remove('d-none');
                selectedCountSpan.textContent = checkedCount;
            } else {
                bulkActionBar.classList.add('d-none');
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                updateBulkBar();
            });
        }

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateBulkBar();
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length;
                }
            });
        });

        // Search Input Delay Submit
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timer;

        if (searchInput) {
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;

            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            });
        }

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