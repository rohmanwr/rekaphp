@extends('layouts.app')

@section('title', 'Dashboard - Rekap Bisnis HP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Dashboard Rekap</h3>
    <span class="badge bg-secondary fs-6"><i class="bi bi-calendar-event"></i> {{ date('F Y') }}</span>
</div>

<!-- Pesan Notifikasi -->
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

<!-- 4 Widget Card Dashboard Utama -->
<div class="row g-3 mb-4">
    <!-- 1. Total Jumlah Pembelian -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase opacity-75 mb-1 fs-7">Total Qty Pembelian</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($totalJumlahPembelian ?? 0, 0, ',', '.') }} Unit</h3>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Total Nominal Pembelian Perbulan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase opacity-75 mb-1 fs-7">Pembelian (Bulan Ini)</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalNominalPembelianBulanIni ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="bi bi-cart-down"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Total Nominal Penjualan Perbulan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-info text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase opacity-75 mb-1 fs-7">Penjualan (Bulan Ini)</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalNominalPenjualanBulanIni ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Total Profit Bersih Perbulan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 bg-success text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase opacity-75 mb-1 fs-7">Profit Bersih (Bulan Ini)</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalProfitBersihBulanIni ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection