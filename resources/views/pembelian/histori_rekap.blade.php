@extends('layouts.app')

@section('title', 'Histori Rekap Pembelian')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Histori Rekap Pembelian</h3>
        <p class="text-muted small mb-0">Arsip data rekap pembelian yang sudah Selesai atau diproses sebagai Retur.</p>
    </div>
    <div class="d-grid d-sm-flex align-items-center gap-2 w-100 w-md-auto">
        <!-- Tombol Khusus Cek IMEI Duplikat -->
        <button type="button" class="btn btn-warning fw-bold text-dark shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#modalCekDuplikat" onclick="jalankanCekDuplikatIMEI()">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Cek IMEI Duplikat
        </button>
    </div>
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

<!-- Searchbar Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="{{ route('pembelian.histori_rekap') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan Kode, No. Pesanan, Alamat, Nama Barang, Toko, atau IMEI..."
                    value="{{ $search ?? '' }}"
                    autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('pembelian.histori_rekap') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- TAMPILAN 1: Tabel Histori Desktop / Tablet (d-none d-md-block) -->
<div class="card border-0 shadow-sm mb-4 d-none d-md-block">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelHistoriRekapDesktop">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode & No. Pesanan</th>
                        <th>Barang, Toko & Alamat</th>
                        <th>IMEI / Serial</th>
                        <th>Via</th>
                        <th>Tanggal Beli / Terbit</th>
                        <th>No. Invoice</th>
                        <th>Total Modal</th>
                        <th>Harga Jual</th>
                        <th>Total Profit</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $index => $item)
                    <tr class="row-rekap-item row-rekap-desktop"
                        data-detail-imei="{{ trim($item->detail_imei ?? '') }}"
                        data-nama-barang="{{ $item->nama_barang }}"
                        data-kode-otomatis="{{ $item->kode_otomatis }}"
                        data-tanggal-beli="{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}"
                        data-tanggal-terbit="{{ !empty($item->tanggal_terbit) ? \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') : '-' }}">
                        <td class="text-center fw-semibold text-muted">
                            @if(method_exists($pembelians, 'firstItem'))
                            {{ $pembelians->firstItem() + $index }}
                            @else
                            {{ $loop->iteration }}
                            @endif
                        </td>

                        <!-- Kode Sistem & No. Pesanan -->
                        <td>
                            <span class="badge bg-dark mb-1 d-inline-block">{{ $item->kode_otomatis }}</span><br>
                            @if($item->status === 'Retur')
                            <span class="badge bg-danger mb-1"><i class="bi bi-arrow-return-left me-1"></i>Retur</span><br>
                            @endif
                            <small class="text-muted"><i class="bi bi-hash"></i> {{ $item->kode_manual ?? '-' }}</small>
                        </td>

                        <!-- Barang, Toko & Alamat -->
                        <td>
                            <strong>{{ $item->nama_barang }}</strong><br>
                            <small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small><br>
                            <small class="text-danger"><i class="bi bi-geo-alt"></i> {{ $item->nama_alamat ?? '-' }}</small>
                        </td>

                        <!-- IMEI / Serial -->
                        <td>
                            @if(!empty($item->detail_imei))
                            <span class="font-monospace text-dark small bg-light p-1 rounded border d-inline-block text-break" style="white-space: pre-line;">{{ $item->detail_imei }}</span>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Via -->
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

                        <!-- Tanggal Beli & Tanggal Terbit -->
                        <td>
                            <small class="d-block"><strong>Beli:</strong> {{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</small>
                            <small class="text-muted">
                                <strong>Terbit:</strong>
                                @if(!empty($item->tanggal_terbit)) {{ \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') }}
                                @else -
                                @endif
                            </small>
                        </td>

                        <!-- No. Invoice -->
                        <td>
                            @if(!empty($item->no_invoice))
                            <span class="badge bg-secondary font-monospace">{{ $item->no_invoice }}</span>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <!-- Total Modal -->
                        <td class="fw-bold text-secondary">Rp {{ number_format($item->total_modal ?? 0, 0, ',', '.') }}</td>

                        <!-- Harga Jual -->
                        <td class="fw-bold text-success">
                            Rp {{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}
                        </td>

                        <!-- Total Profit -->
                        <td class="fw-bold {{ ($item->total_profit ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($item->total_profit ?? 0, 0, ',', '.') }}
                        </td>

                        <!-- Aksi Kembalikan Status -->
                        <td class="text-center">
                            @if($item->invoice_locked)
                            <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                            @else
                            <div class="d-grid gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditHistori{{ $item->id }}" title="Edit data rekap">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalRollbackStatus{{ $item->id }}" title="Kembalikan Status Ke Rekap Aktif">
                                    <i class="bi bi-arrow-counterclockwise"></i> Batal Selesai
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada data rekap pembelian berstatus Selesai atau Retur.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAMPILAN 2: Mode HP / Seluler Card (d-block d-md-none) -->
<div class="d-block d-md-none mb-4">
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
    <div class="card border-0 shadow-sm mb-3 row-rekap-item row-rekap-mobile"
        data-detail-imei="{{ trim($item->detail_imei ?? '') }}"
        data-nama-barang="{{ $item->nama_barang }}"
        data-kode-otomatis="{{ $item->kode_otomatis }}"
        data-tanggal-beli="{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}"
        data-tanggal-terbit="{{ !empty($item->tanggal_terbit) ? \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') : '-' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2 border-bottom pb-2">
                <div>
                    <span class="badge bg-dark font-monospace me-1">{{ $item->kode_otomatis }}</span>
                    @if($item->status === 'Retur')
                    <span class="badge bg-danger"><i class="bi bi-arrow-return-left me-1"></i>Retur</span>
                    @endif
                    @if(!empty($item->no_invoice))
                    <span class="badge bg-secondary font-monospace">{{ $item->no_invoice }}</span>
                    @endif
                </div>
                <span class="badge {{ $badgeColor }}">{{ $item->via }}</span>
            </div>

            <div class="mb-2">
                <h6 class="fw-bold mb-1 text-dark">{{ $item->nama_barang }}</h6>
                <div class="text-muted small"><i class="bi bi-shop me-1"></i>{{ $item->nama_toko }} @if(!empty($item->nama_alamat)) | <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $item->nama_alamat }} @endif</div>
            </div>

            @if(!empty($item->detail_imei))
            <div class="bg-light rounded p-2 border mb-2 font-monospace small text-break" style="white-space: pre-line;">
                <i class="bi bi-barcode me-1 text-muted"></i>{{ $item->detail_imei }}
            </div>
            @endif

            <div class="row g-1 bg-light rounded p-2 my-2 small">
                <div class="col-6">
                    <span class="text-muted d-block fs-7">Modal:</span>
                    <strong class="text-secondary">Rp {{ number_format($item->total_modal ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted d-block fs-7">Harga Jual:</span>
                    <strong class="text-success">Rp {{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="col-12 mt-1 border-top pt-1 d-flex justify-content-between align-items-center">
                    <span class="text-muted fs-7">Total Profit:</span>
                    <strong class="{{ ($item->total_profit ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">
                        Rp {{ number_format($item->total_profit ?? 0, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                <div class="small text-muted fs-7">
                    <i class="bi bi-calendar-event me-1"></i>Beli: {{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}
                </div>
                @if($item->invoice_locked)
                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>
                @else
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditHistori{{ $item->id }}" title="Edit data rekap" aria-label="Edit data rekap">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold px-3" data-bs-toggle="modal" data-bs-target="#modalRollbackStatus{{ $item->id }}">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Batal Selesai
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card border-0 shadow-sm p-4 text-center text-muted">
        <i class="bi bi-archive fs-1 d-block mb-2 text-secondary"></i>
        Belum ada data rekap pembelian berstatus Selesai atau Retur.
    </div>
    @endforelse
</div>

<!-- MODAL POPUP KEMBALIKAN STATUS (ROLLBACK) UNTUK SETIAP ITEM -->
@foreach($pembelians as $item)
@if(!$item->invoice_locked)
<div class="modal fade" id="modalEditHistori{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Histori Rekap</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pembelian.histori_rekap.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        Harga jual, tanggal beli/terbit, dan nomor invoice tidak diubah. Perubahan data barang akan ikut tampil pada rincian invoice.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode / No. Pesanan</label>
                            <input type="text" name="kode_manual" class="form-control" value="{{ $item->kode_manual }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Barang</label>
                            <input type="text" name="nama_barang" class="form-control" value="{{ $item->nama_barang }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Perangkat</label>
                            <input type="text" name="nama_device" class="form-control" value="{{ $item->nama_device }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Toko</label>
                            <input type="text" name="nama_toko" class="form-control" value="{{ $item->nama_toko }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Alamat</label>
                            <input type="text" name="nama_alamat" class="form-control" value="{{ $item->nama_alamat }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Via Pembelian</label>
                            <input type="text" name="via" class="form-control" value="{{ $item->via }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">IMEI / Serial</label>
                            <textarea name="detail_imei" class="form-control" rows="3">{{ $item->detail_imei }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Total Modal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="total_modal" class="form-control" min="0" step="0.01" value="{{ $item->total_modal }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRollbackStatus{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark py-2">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Kembalikan Status Transaksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @php
            $routeRollback = Route::has('pembelian.restoreStatus')
            ? route('pembelian.restoreStatus', $item->id)
            : route('pembelian.updateStatus', $item->id);
            @endphp
            <form action="{{ $routeRollback }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body p-3">
                    <div class="alert alert-warning py-2 mb-3 small">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Mengembalikan transaksi ini akan membatalkan status <strong>Selesai</strong> dan mengizinkan Anda memproses/edit ulang data transaksi ini.
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Kode / Barang:</label>
                        <div class="p-2 bg-light border rounded fw-bold text-dark small">
                            [{{ $item->kode_otomatis }}] {{ $item->nama_barang }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kembalikan Ke Status Baru:</label>
                        <select name="status" class="form-select form-select-sm fw-semibold" required>
                            <option value="Sudah Ready" selected>✅ Sudah Ready (Siap Dijual Kembali)</option>
                            <option value="Sudah Diambil">📦 Sudah Diambil</option>
                            <option value="Belum Ready">⏳ Belum Ready</option>
                            <option value="Bermasalah">⚠️ Bermasalah</option>
                        </select>
                        <small class="text-muted fs-7 mt-1 d-block">
                            *Data Invoice lama & nilai profit transaksi ini akan di-reset agar dapat diperbarui dengan data baru.
                        </small>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold px-3">
                        <i class="bi bi-check-circle me-1"></i> Proses Kembalikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

<!-- MODAL POPUP CEK IMEI DUPLIKAT -->
<div class="modal fade" id="modalCekDuplikat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-shield-exclamation me-2"></i> Laporan Pengecekan IMEI Duplikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Status Alert Hasil Cek -->
                <div id="alertSummaryDuplikat" class="alert d-flex align-items-center mb-3" role="alert">
                    <i id="iconSummaryDuplikat" class="fs-3 me-3"></i>
                    <div>
                        <strong id="titleSummaryDuplikat"></strong>
                        <div id="descSummaryDuplikat" class="small"></div>
                    </div>
                </div>

                <!-- Kontainer Rincian Data Duplikat -->
                <div id="containerRincianDuplikat" class="d-none">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-list-task me-1"></i> Rincian IMEI / Serial Number Double:</h6>
                    <div class="table-responsive border rounded" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-striped align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;" class="text-center">No</th>
                                    <th>Nomor IMEI / Serial</th>
                                    <th class="text-center" style="width: 110px;">Kemunculan</th>
                                    <th>Item Terkait & Tanggal</th>
                                </tr>
                            </thead>
                            <tbody id="bodyTabelDuplikat">
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
    function jalankanCekDuplikatIMEI() {
        const isMobile = window.innerWidth < 768;
        const targetSelector = isMobile ? '.row-rekap-mobile' : '.row-rekap-desktop';
        const rows = document.querySelectorAll(targetSelector);

        let mapImei = {};

        rows.forEach(function(row) {
            const rawImei = row.getAttribute('data-detail-imei');
            const barang = row.getAttribute('data-nama-barang');
            const kodeOtomatis = row.getAttribute('data-kode-otomatis');
            const tglBeli = row.getAttribute('data-tanggal-beli');
            const tglTerbit = row.getAttribute('data-tanggal-terbit');

            if (rawImei && rawImei !== '-' && rawImei !== '') {
                const listImei = rawImei.split(/[\n,]+/).map(function(s) {
                    return s.trim();
                }).filter(function(s) {
                    return s !== '';
                });

                listImei.forEach(function(imei) {
                    if (imei && imei !== '-') {
                        if (!mapImei[imei]) {
                            mapImei[imei] = [];
                        }
                        mapImei[imei].push({
                            barang: barang,
                            kodeOtomatis: kodeOtomatis,
                            tglBeli: tglBeli,
                            tglTerbit: tglTerbit
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

        const alertBox = document.getElementById('alertSummaryDuplikat');
        const iconBox = document.getElementById('iconSummaryDuplikat');
        const titleBox = document.getElementById('titleSummaryDuplikat');
        const descBox = document.getElementById('descSummaryDuplikat');
        const containerRincian = document.getElementById('containerRincianDuplikat');
        const bodyTabel = document.getElementById('bodyTabelDuplikat');

        if (!bodyTabel || !alertBox || !iconBox || !titleBox || !descBox || !containerRincian) {
            return;
        }

        bodyTabel.innerHTML = '';

        if (listDuplikatImei.length === 0) {
            alertBox.className = 'alert alert-success d-flex align-items-center mb-3';
            iconBox.className = 'bi bi-check-circle-fill fs-3 me-3 text-success';
            titleBox.innerText = 'Data IMEI Bersih / Tidak Ada Duplikat!';
            descBox.innerText = 'Semua nomor IMEI pada halaman histori ini unik dan tidak ditemukan inputan ganda.';
            containerRincian.classList.add('d-none');
        } else {
            alertBox.className = 'alert alert-danger d-flex align-items-center mb-3';
            iconBox.className = 'bi bi-exclamation-triangle-fill fs-3 me-3 text-danger';
            titleBox.innerText = 'Ditemukan ' + listDuplikatImei.length + ' IMEI Duplikat!';
            descBox.innerText = 'Silakan periksa daftar nomor IMEI yang terinput lebih dari 1 kali di bawah ini:';
            containerRincian.classList.remove('d-none');

            listDuplikatImei.forEach(function(item, index) {
                let detailItemsHtml = item.items.map(function(it) {
                    return '<div class="mb-1 pb-1 border-bottom border-light-subtle">' +
                        '<strong>' + it.kodeOtomatis + '</strong> (' + it.barang + ')' +
                        '<br>' +
                        '<small class="text-muted">' +
                        '<i class="bi bi-calendar-event me-1"></i>Beli: <strong>' + it.tglBeli + '</strong> | Terbit: <strong>' + it.tglTerbit + '</strong>' +
                        '</small>' +
                        '</div>';
                }).join('');

                let tr = document.createElement('tr');
                tr.innerHTML = '<td class="text-center text-muted fw-semibold">' + (index + 1) + '</td>' +
                    '<td class="fw-bold font-monospace text-danger align-middle">' + item.val + '</td>' +
                    '<td class="text-center align-middle"><span class="badge bg-danger">' + item.count + 'x Muncul</span></td>' +
                    '<td class="text-dark">' + detailItemsHtml + '</td>';

                bodyTabel.appendChild(tr);
            });
        }
    }
</script>
@endsection