@extends('layouts.app')

@section('title', 'Dashboard Rekap Bisnis')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 pb-2 border-bottom">
    <div>
        <h3 class="fw-bold text-dark mb-1">Dashboard Bisnis</h3>
        <p class="text-muted small mb-0">Ringkasan pembelian, penjualan, profit, dan posisi keuangan usaha.</p>
    </div>
    <a href="{{ route('penjualan.histori') }}" class="btn btn-sm btn-outline-primary mt-2 mt-md-0">
        <i class="bi bi-receipt me-1"></i> Histori Penjualan
    </a>
</div>

<form action="{{ route('rekap.index') }}" method="GET" id="dashboardFilterForm" class="dashboard-toolbar p-3 mb-3">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div class="row g-2 flex-grow-1">
            <div class="col-6 col-md-4">
                <label for="startDate" class="form-label small fw-semibold text-secondary mb-1">Dari tanggal</label>
                <input id="startDate" type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
            </div>
            <div class="col-6 col-md-4">
                <label for="endDate" class="form-label small fw-semibold text-secondary mb-1">Sampai tanggal</label>
                <input id="endDate" type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
            </div>
            <div class="col-12 col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 fw-semibold"><i class="bi bi-funnel me-1"></i>Terapkan</button>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2" aria-label="Pilih rentang cepat">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-period="7">7 hari</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-period="30">30 hari</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-period="month">Bulan ini</button>
            <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-light border" title="Reset periode"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </div>
</form>

<div class="small text-muted mb-3">
    Periode aktif: <strong class="text-dark">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
</div>

<div class="row g-3" aria-label="Ringkasan utama">

    <!-- 1. TOTAL QTY PEMBELIAN (HISTORI REKAP) -->
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-primary border-4">
            <div class="card-body d-flex flex-column justify-content-between p-3">
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
            </div>
        </div>
    </div>

    <!-- 2. TOTAL PEMBELIAN (NOMINAL) -->
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-success border-4">
            <div class="card-body d-flex flex-column justify-content-between p-3">
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
            </div>
        </div>
    </div>

    <!-- 3. TOTAL PENJUALAN -->
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-warning border-4">
            <div class="card-body d-flex flex-column justify-content-between p-3">
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
            </div>
        </div>
    </div>

    <!-- 4. TOTAL PROFIT -->
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 card-dashboard border-start border-info border-4">
            <div class="card-body d-flex flex-column justify-content-between p-3">
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
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mt-1 mb-3">
    <div class="col-xl-8">
        <section class="dashboard-panel p-3 h-100" aria-labelledby="trendHeading">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <h5 id="trendHeading" class="fw-bold mb-1">Tren penjualan & profit</h5>
                    <p class="small text-muted mb-0">Pergerakan enam bulan terakhir setelah retur.</p>
                </div>
                <span class="badge bg-light text-secondary border">6 bulan</span>
            </div>
            <div class="dashboard-chart-wrap">
                <canvas id="businessTrendChart" data-trend="{{ json_encode($trendData) }}" role="img" aria-label="Grafik tren penjualan bersih dan profit enam bulan terakhir"></canvas>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="dashboard-panel h-100" aria-labelledby="businessSummaryHeading">
            <div class="p-3 border-bottom">
                <h5 id="businessSummaryHeading" class="fw-bold mb-0">Posisi & tindak lanjut</h5>
            </div>
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="small text-muted">Aset bersih tercatat</div>
                    <div class="fw-bold text-dark fs-5">Rp {{ number_format($totalBersihAset, 0, ',', '.') }}</div>
                    <div class="small text-muted">Pembaruan {{ \Carbon\Carbon::parse($tanggalInput)->format('d/m/Y') }}</div>
                </div>
                <a href="{{ route('keuangan.index') }}" class="btn btn-sm btn-outline-success" title="Buka keuangan" aria-label="Buka keuangan"><i class="bi bi-arrow-up-right"></i></a>
            </div>
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="small text-muted">Nilai retur periode ini</div>
                    <div class="fw-bold text-danger">Rp {{ number_format($totalRetur, 0, ',', '.') }}</div>
                </div>
                <i class="bi bi-arrow-return-left text-danger fs-4"></i>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="small text-muted">Belum lunas · {{ number_format($jumlahTagihanBelumLunas) }} invoice</div>
                    <div class="fw-bold text-warning-emphasis">Rp {{ number_format($totalTagihanBelumLunas, 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('penjualan.histori') }}" class="btn btn-sm btn-outline-warning" title="Periksa invoice" aria-label="Periksa invoice"><i class="bi bi-arrow-up-right"></i></a>
            </div>
        </section>
    </div>
</div>

<section class="dashboard-panel overflow-hidden mb-3" aria-labelledby="recentInvoicesHeading">
    <div class="d-flex justify-content-between align-items-center gap-2 p-3 border-bottom">
        <div>
            <h5 id="recentInvoicesHeading" class="fw-bold mb-1">Invoice terbaru</h5>
            <p class="small text-muted mb-0">Maksimal enam invoice pada periode aktif.</p>
        </div>
        <a href="{{ route('penjualan.histori') }}" class="btn btn-sm btn-outline-primary">Lihat histori</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 recent-invoices">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th class="text-end">Nilai bersih</th>
                    <th class="text-center">Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInvoices as $invoice)
                <tr>
                    <td data-label="Invoice" class="fw-semibold">{{ $invoice->referensi }}</td>
                    <td data-label="Pelanggan">{{ $invoice->nama_pelanggan }}</td>
                    <td data-label="Tanggal">{{ \Carbon\Carbon::parse($invoice->tanggal)->format('d/m/Y') }}</td>
                    <td data-label="Nilai bersih" class="text-end fw-semibold">Rp {{ number_format(max(0, (float) $invoice->total - (float) ($invoice->retur_total_nilai ?? 0)), 0, ',', '.') }}</td>
                    <td data-label="Pembayaran" class="text-center">
                        @if($invoice->status_payment === 'sudah')
                        <span class="badge text-bg-success">Lunas</span>
                        @else
                        <span class="badge text-bg-warning">Belum lunas</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada invoice pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- Styling CSS Tambahan -->
<style>
    .dashboard-toolbar,
    .dashboard-panel {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: #fff;
    }

    .card-dashboard {
        min-width: 0;
        border-radius: 8px;
    }

    .card-dashboard h4 {
        overflow-wrap: anywhere;
        font-size: 1rem;
    }

    .dashboard-chart-wrap {
        position: relative;
        height: 290px;
    }

    .recent-invoices td,
    .recent-invoices th {
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .dashboard-toolbar .form-control {
            min-height: 42px;
        }

        .dashboard-chart-wrap {
            height: 240px;
        }

        .recent-invoices {
            display: block;
        }

        .recent-invoices thead {
            display: none;
        }

        .recent-invoices tbody {
            display: grid;
            gap: .65rem;
            padding: .65rem;
        }

        .recent-invoices tbody tr {
            display: block;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }

        .recent-invoices tbody td {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            padding: .55rem .65rem;
            border: 0;
            border-bottom: 1px solid #edf0f2;
            text-align: right !important;
        }

        .recent-invoices tbody td::before {
            content: attr(data-label);
            flex: 0 0 35%;
            color: #6c757d;
            font-size: .78rem;
            font-weight: 700;
            text-align: left;
        }

        .recent-invoices tbody td:last-child {
            border-bottom: 0;
        }

        .recent-invoices tbody td[colspan] {
            display: block;
            text-align: center !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('dashboardFilterForm');
        const startInput = document.getElementById('startDate');
        const endInput = document.getElementById('endDate');

        startInput.addEventListener('change', function() {
            endInput.min = this.value;
        });

        endInput.addEventListener('change', function() {
            startInput.max = this.value;
        });

        document.querySelectorAll('[data-period]').forEach(function(button) {
            button.addEventListener('click', function() {
                const today = new Date();
                const start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                const period = this.dataset.period;

                if (period === 'month') {
                    start.setDate(1);
                } else {
                    start.setDate(start.getDate() - (Number(period) - 1));
                }

                const toDateInput = function(date) {
                    return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
                };

                startInput.value = toDateInput(start);
                endInput.value = toDateInput(today);
                startInput.max = endInput.value;
                endInput.min = startInput.value;
                filterForm.requestSubmit();
            });
        });

        const chartElement = document.getElementById('businessTrendChart');
        if (chartElement && window.Chart) {
            const trend = JSON.parse(chartElement.dataset.trend);
            const compactCurrency = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                notation: 'compact',
                maximumFractionDigits: 1
            });

            new Chart(chartElement, {
                data: {
                    labels: trend.labels,
                    datasets: [{
                            type: 'bar',
                            label: 'Penjualan bersih',
                            data: trend.sales,
                            backgroundColor: 'rgba(13, 110, 253, .72)',
                            borderRadius: 4,
                            maxBarThickness: 34
                        },
                        {
                            type: 'line',
                            label: 'Profit bersih',
                            data: trend.profit,
                            borderColor: '#e07820',
                            backgroundColor: '#e07820',
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            tension: .3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + compactCurrency.format(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return compactCurrency.format(value);
                                }
                            },
                            grid: {
                                color: 'rgba(108, 117, 125, .12)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection