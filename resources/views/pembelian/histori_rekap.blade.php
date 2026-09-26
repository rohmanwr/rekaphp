@extends('layouts.app')

@section('title', 'Histori Rekap Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Histori Rekap Pembelian</h3>
        <p class="text-muted small mb-0">Arsip data rekap pembelian yang statusnya sudah Selesai.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Searchbar Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
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

<!-- Tabel Histori Rekap Ringkas -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $index => $item)
                    <tr>
                        <td class="text-center fw-semibold text-muted">
                            {{ $loop->iteration }}
                        </td>

                        <!-- Digabung: Kode Sistem & No. Pesanan -->
                        <td>
                            <span class="badge bg-dark mb-1 d-inline-block">{{ $item->kode_otomatis }}</span><br>
                            <small class="text-muted"><i class="bi bi-hash"></i> {{ $item->kode_manual ?? '-' }}</small>
                        </td>

                        <!-- Digabung: Barang, Toko & Alamat -->
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

                        <!-- Digabung: Tanggal Beli & Tanggal Terbit -->
                        <td>
                            <small class="d-block"><strong>Beli:</strong> {{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</small>
                            <small class="text-muted">
                                <strong>Terbit:</strong>
                                @if(!empty($item->tanggal_terbit))
                                {{ \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') }}
                                @else
                                -
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada data rekap pembelian dengan status Selesai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection