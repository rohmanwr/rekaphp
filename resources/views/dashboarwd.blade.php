@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Dashboard Overview</h3>
</div>

<div class="row g-4">

    <!-- 1. TOTAL QTY PEMBELIAN -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Qty Pembelian</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalQtyPembelian, 0, ',', '.') }} Unit</h4>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                </div>
                <!-- Filter Tanggal Pembelian Qty -->
                <form action="{{ route('dashboard') }}" method="GET" class="mt-3 pt-2 border-top">
                    <div class="row g-1">
                        <div class="col-6"><input type="date" name="start_date_pembelian" class="form-control form-control-xs" value="{{ request('start_date_pembelian') }}" style="font-size: 0.75rem;"></div>
                        <div class="col-6"><input type="date" name="end_date_pembelian" class="form-control form-control-xs" value="{{ request('end_date_pembelian') }}" style="font-size: 0.75rem;"></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100 mt-2" style="font-size: 0.75rem;">Filter Qty</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. TOTAL PEMBELIAN (NOMINAL) -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Pembelian</span>
                        <h5 class="fw-bold text-dark mb-0 mt-1">Rp {{ number_format($totalNominalPembelian, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-3">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                </div>
                <!-- Filter Tanggal Pembelian Nominal (Menggunakan parameter tersendiri) -->
                <form action="{{ route('dashboard') }}" method="GET" class="mt-3 pt-2 border-top">
                    <div class="row g-1">
                        <div class="col-6"><input type="date" name="start_date_nominal" class="form-control form-control-xs" value="{{ request('start_date_nominal') }}" style="font-size: 0.75rem;"></div>
                        <div class="col-6"><input type="date" name="end_date_nominal" class="form-control form-control-xs" value="{{ request('end_date_nominal') }}" style="font-size: 0.75rem;"></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-success w-100 mt-2" style="font-size: 0.75rem;">Filter Pembelian</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. TOTAL PENJUALAN -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Penjualan</span>
                        <h5 class="fw-bold text-primary mb-0 mt-1">Rp {{ number_format($totalPenjualan ?? $totalNominalPenjualan, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-3">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                </div>
                <!-- Filter Tanggal Penjualan -->
                <form action="{{ route('dashboard') }}" method="GET" class="mt-3 pt-2 border-top">
                    <div class="row g-1">
                        <div class="col-6"><input type="date" name="start_date_penjualan" class="form-control form-control-xs" value="{{ request('start_date_penjualan') }}" style="font-size: 0.75rem;"></div>
                        <div class="col-6"><input type="date" name="end_date_penjualan" class="form-control form-control-xs" value="{{ request('end_date_penjualan') }}" style="font-size: 0.75rem;"></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-warning text-dark w-100 mt-2" style="font-size: 0.75rem;">Filter Penjualan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. TOTAL PROFIT -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Profit</span>
                        <h5 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($totalProfit ?? $totalProfitBersih, 0, ',', '.') }}</h5>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <!-- Filter Tanggal Profit -->
                <form action="{{ route('dashboard') }}" method="GET" class="mt-3 pt-2 border-top">
                    <div class="row g-1">
                        <div class="col-6"><input type="date" name="start_date_profit" class="form-control form-control-xs" value="{{ request('start_date_profit') }}" style="font-size: 0.75rem;"></div>
                        <div class="col-6"><input type="date" name="end_date_profit" class="form-control form-control-xs" value="{{ request('end_date_profit') }}" style="font-size: 0.75rem;"></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-info w-100 mt-2" style="font-size: 0.75rem;">Filter Profit</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection