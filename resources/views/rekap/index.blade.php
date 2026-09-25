@extends('layouts.app')

@section('title', 'Dashboard Rekap Bisnis')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-2 border-bottom">
    <div>
        <h3 class="fw-bold text-dark mb-1">Dashboard Ringkasan Bisnis</h3>
        <p class="text-muted small mb-0">Pantau performa penjualan, total modal, dan perolehan laba secara real-time.</p>
    </div>
    <div class="mt-3 mt-md-0">
        <!-- Tombol Reset untuk membersihkan input dan kembali ke 0 -->
        <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-light border shadow-sm text-secondary fw-semibold">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Semua Filter
        </a>
    </div>
</div>

<!-- Form Global Tunggal -->
<form action="{{ route('rekap.index') }}" method="GET" id="dashboardFilterForm">
    <div class="row g-4">

        <!-- 1. TOTAL QTY PEMBELIAN (HISTORI REKAP) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-primary border-4">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold tracking-wider">Qty Histori Rekap</span>
                                <h3 class="fw-extrabold text-dark mb-0 mt-1">{{ number_format($totalQtyPembelian ?? 0, 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">Unit</span></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-4 shadow-xs">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-light">
                        <label class="form-label text-muted fs-7 fw-semibold mb-1">Filter Tanggal Beli:</label>
                        <div class="row g-1 mb-2">
                            <div class="col-6"><input type="date" name="start_date_pembelian" class="form-control form-control-sm bg-light border-0" value="{{ request('start_date_pembelian') }}"></div>
                            <div class="col-6"><input type="date" name="end_date_pembelian" class="form-control form-control-sm bg-light border-0" value="{{ request('end_date_pembelian') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold rounded-pill shadow-xs">
                            <i class="bi bi-filter me-1"></i> Terapkan Filter Qty
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. TOTAL PEMBELIAN (NOMINAL) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-success border-4">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold tracking-wider">Total Pembelian</span>
                                <h4 class="fw-extrabold text-success mb-0 mt-1">Rp {{ number_format($totalNominalPembelian ?? 0, 0, ',', '.') }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 shadow-xs">
                                <i class="bi bi-wallet2 fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-light">
                        <label class="form-label text-muted fs-7 fw-semibold mb-1">Filter Tanggal Beli:</label>
                        <div class="row g-1 mb-2">
                            <div class="col-6"><input type="date" name="start_date_nominal" class="form-control form-control-sm bg-light border-0" value="{{ request('start_date_nominal') }}"></div>
                            <div class="col-6"><input type="date" name="end_date_nominal" class="form-control form-control-sm bg-light border-0" value="{{ request('end_date_nominal') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success w-100 fw-semibold rounded-pill shadow-xs">
                            <i class="bi bi-filter me-1"></i> Terapkan Filter Modal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TOTAL PENJUALAN -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-warning border-4">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold tracking-wider">Total Penjualan</span>
                                <h4 class="fw-extrabold text-dark mb-0 mt-1">Rp {{ number_format($totalPenjualan ?? 0, 0, ',', '.') }}</h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-4 shadow-xs">
                                <i class="bi bi-cart-check fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-light">
                        <label class="form-label text-muted fs-7 fw-semibold mb-1">Filter Tanggal Invoice:</label>
                        <div class="row g-1 mb-2">
                            <div class="col-6"><input type="date" name="start_date_penjualan" class="form-control form-control-sm bg-light border-0" value="{{ request('start_date_penjualan') }}"></div>
                            <div class="col-6"><input type="date" name="end_date_penjualan" class="form-control form-control-sm bg-light border-0" value="{{ request('end_date_penjualan') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-warning text-dark w-100 fw-semibold rounded-pill shadow-xs">
                            <i class="bi bi-filter me-1"></i> Terapkan Filter Penjualan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. TOTAL PROFIT -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-info border-4">
                <div class="card-body d-flex flex-column justify-content-between p-4">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold tracking-wider">Total Profit Bersih</span>
                                <h4 class="fw-extrabold text-primary mb-0 mt-1">Rp {{ number_format($totalProfit ?? 0, 0, ',', '.') }}</h4>
                            </div>
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-4 shadow-xs">
                                <i class="bi bi-graph-up-arrow fs-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-light">
                        <label class="form-label text-muted fs-7 fw-semibold mb-1">Filter Tanggal Profit:</label>
                        <div class="row g-1 mb-2">
                            <div class="col-6"><input type="date" name="start_date_profit" class="form-control form-control-sm bg-light border-0" value="{{ request('start_date_profit') }}"></div>
                            <div class="col-6"><input type="date" name="end_date_profit" class="form-control form-control-sm bg-light border-0" value="{{ request('end_date_profit') }}"></div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-info text-white w-100 fw-semibold rounded-pill shadow-xs">
                            <i class="bi bi-filter me-1"></i> Terapkan Filter Profit
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- Styling CSS Tambahan -->
<style>
    .card-dashboard {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 12px;
    }

    .card-dashboard:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .08) !important;
    }

    .fs-7 {
        font-size: 0.75rem;
    }

    .fw-extrabold {
        font-weight: 800;
    }

    .tracking-wider {
        letter-spacing: 0.5px;
    }

    .form-control-sm {
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
    }
</style>
@endsection