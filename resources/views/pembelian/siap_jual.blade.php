@extends('layouts.app')

@section('title', 'Daftar Barang Siap Jual')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Barang Siap Jual</h3>
        <p class="text-muted small mb-0">Daftar barang dari rekap pembelian yang statusnya sudah diubah menjadi Jual.</p>
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
    <div class="card-body">
        <form action="{{ route('pembelian.siap_jual') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan Kode, Nama Barang, atau Toko..."
                    value="{{ $search ?? '' }}"
                    autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('pembelian.siap_jual') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Form untuk Pemilihan / Aksi Invoice Massal -->
<form action="{{ route('invoice.create') }}" method="POST">
    @csrf
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <button type="submit" class="btn btn-success fw-semibold">
                <i class="bi bi-receipt-cutoff me-1"></i> Buat Invoice Terpilih
            </button>
        </div>
    </div>

    <!-- Tabel Barang Siap Jual -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th>Kode Sistem</th>
                            <th>Barang & Toko</th>
                            <th>Via</th>
                            <th>Tgl Beli</th>
                            <th>Total Modal</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembelians as $index => $item)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="pembelian_ids[]" value="{{ $item->id }}" class="form-check-input item-checkbox">
                            </td>
                            <td class="text-center fw-semibold text-muted">
                                {{ $loop->iteration }}
                            </td>
                            <td><span class="badge bg-dark">{{ $item->kode_otomatis }}</span></td>
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
                            <td class="fw-bold text-success">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                    <i class="bi bi-tag-fill"></i> Siap Jual
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada barang yang berstatus "Jual". Silakan ubah status pada menu Rekap Pembelian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
</script>
@endsection