@extends('layouts.app')

@section('title', 'Rekap Pembelian HP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Rekap Pembelian</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPembelian">
        <i class="bi bi-plus-lg"></i> Tambah Pembelian
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Searchbar Filter (Auto-search on type) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form id="searchForm" action="{{ route('pembelian.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari otomatis berdasarkan Kode TRX, Nama Barang, Toko, atau IMEI..."
                    value="{{ $search }}"
                    autocomplete="off"
                    autofocus>
                @if(!empty($search))
                <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Rekap Pembelian -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Sistem</th>
                        <th>Kode Manual</th>
                        <th>Barang & Toko</th>
                        <th>Via</th>
                        <th>Tgl Beli</th>
                        <th>Total Modal</th>
                        <th>Deskripsi / IMEI</th>
                        <th>Status Ready</th>
                        <th>Lampiran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $item)
                    <tr>
                        <td><span class="badge bg-dark">{{ $item->kode_otomatis }}</span></td>
                        <td>{{ $item->kode_manual ?? '-' }}</td>
                        <td>
                            <strong>{{ $item->nama_barang }}</strong><br>
                            <small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
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
                        <td><small class="text-break">{{ $item->deskripsi_imei ?? '-' }}</small></td>
                        <td>
                            <form action="{{ route('pembelian.toggleReady', $item->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @if($item->is_ready)
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                    <i class="bi bi-check-circle-fill"></i> Ready
                                </button>
                                @else
                                <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                    <i class="bi bi-clock-history"></i> Belum Ready
                                </button>
                                @endif
                            </form>
                        </td>
                        <td>
                            @if($item->file_lampiran)
                            <a href="{{ asset('storage/' . $item->file_lampiran) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-paperclip"></i> Lihat
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#modalEditPembelian{{ $item->id }}" title="Edit Data">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Edit Pembelian -->
                    <div class="modal fade" id="modalEditPembelian{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Rekap Pembelian ({{ $item->kode_otomatis }})</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('pembelian.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Kode Transaksi Manual (Opsional)</label>
                                                <input type="text" name="kode_manual" class="form-control" value="{{ old('kode_manual', $item->kode_manual) }}" placeholder="No. Invoice / Resi Toko">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nama Barang Pembelian</label>
                                                <input type="text" name="nama_barang" class="form-control" value="{{ old('nama_barang', $item->nama_barang) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nama Toko Pembelian</label>
                                                <input type="text" name="nama_toko" class="form-control" value="{{ old('nama_toko', $item->nama_toko) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Transaksi Beli Via</label>
                                                <select name="via" class="form-select" required>
                                                    @foreach(['Tokopedia', 'Shopee', 'Lazada', 'TikTok', 'Lainnya'] as $viaOpt)
                                                    <option value="{{ $viaOpt }}" {{ $item->via == $viaOpt ? 'selected' : '' }}>
                                                        {{ $viaOpt == 'Lainnya' ? 'Lainnya / Offline' : $viaOpt }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Beli</label>
                                                <input type="date" name="tanggal_beli" class="form-control" value="{{ old('tanggal_beli', $item->tanggal_beli) }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Total Modal (Rp)</label>
                                                <input type="number" name="total_modal" class="form-control" value="{{ old('total_modal', $item->total_modal) }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Deskripsi / IMEI Pembelian</label>
                                                <textarea name="deskripsi_imei" class="form-control" rows="2">{{ old('deskripsi_imei', $item->deskripsi_imei) }}</textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ganti Lampiran (Opsional)</label>
                                                <input type="file" name="file_lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                                @if($item->file_lampiran)
                                                <small class="text-info d-block mt-1"><i class="bi bi-file-earmark-check"></i> File saat ini sudah terupload</small>
                                                @endif
                                                <small class="text-muted fs-7">Format: JPG, PNG, PDF (Maks. 2MB)</small>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-center mt-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_ready" id="checkReadyEdit{{ $item->id }}" value="1" {{ $item->is_ready ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="checkReadyEdit{{ $item->id }}">Barang Sudah Ready Ditangan?</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-white">Update Pembelian</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Tidak ada data rekap pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pembelians->hasPages())
    <div class="card-footer bg-white">
        {{ $pembelians->appends(['search' => $search])->links() }}
    </div>
    @endif
</div>

<!-- Modal Form Tambah Pembelian -->
<div class="modal fade" id="modalTambahPembelian" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Rekap Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Transaksi Manual (Opsional)</label>
                            <input type="text" name="kode_manual" class="form-control" placeholder="No. Invoice / Resi Toko">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang Pembelian</label>
                            <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: iPhone 13 Pro 128GB" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Toko Pembelian</label>
                            <input type="text" name="nama_toko" class="form-control" placeholder="Nama Toko / Seller" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Transaksi Beli Via</label>
                            <select name="via" class="form-select" required>
                                <option value="Tokopedia">Tokopedia</option>
                                <option value="Shopee">Shopee</option>
                                <option value="Lazada">Lazada</option>
                                <option value="TikTok">TikTok</option>
                                <option value="Lainnya">Lainnya / Offline</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Beli</label>
                            <input type="date" name="tanggal_beli" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Modal (Rp)</label>
                            <input type="number" name="total_modal" class="form-control" placeholder="Misal: 8500000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi / IMEI Pembelian</label>
                            <textarea name="deskripsi_imei" class="form-control" rows="2" placeholder="Masukkan Nomor IMEI 1, IMEI 2, atau kondisi fisik saat dibeli..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Lampiran (Gambar/PDF)</label>
                            <input type="file" name="file_lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted fs-7">Format: JPG, PNG, PDF (Maks. 2MB)</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_ready" id="checkReady" value="1">
                                <label class="form-check-label fw-semibold" for="checkReady">Barang Sudah Ready Ditangan?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script untuk auto search dengan Debounce --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endsection