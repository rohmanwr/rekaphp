@extends('layouts.app')

@section('title', 'Histori Rekap Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Histori Rekap Pembelian</h3>
        <p class="text-muted small mb-0">Arsip data rekap pembelian yang statusnya sudah Selesai.</p>
    </div>
    <div>
        <!-- Tombol Khusus Cek IMEI Duplikat -->
        <button type="button" class="btn btn-warning fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCekDuplikat" onclick="jalankanCekDuplikatIMEI()">
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
            <table class="table table-hover align-middle mb-0" id="tabelHistoriRekap">
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
                    @forelse($pembelians as $index =>$item)
                    <tr class="row-rekap-item"
                        data-detail-imei="{{ trim($item->detail_imei ?? '') }}"
                        data-nama-barang="{{ $item->nama_barang }}"
                        data-kode-otomatis="{{ $item->kode_otomatis }}"
                        data-tanggal-beli="{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}"
                        data-tanggal-terbit="{{ !empty($item->tanggal_terbit) ? \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') : '-' }}">
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
                                @if(!empty($item->tanggal_terbit)) {{ \Carbon\Carbon::parse($item->tanggal_terbit)->format('d/m/Y') }}
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

<!-- MODAL POPUP CEK IMEI DUPLIKAT -->
<div class="modal fade" id="modalCekDuplikat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-exclamation me-2"></i> Laporan Pengecekan IMEI Duplikat</h5>
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
        const rows = document.querySelectorAll('.row-rekap-item');
        let mapImei = {};

        rows.forEach(row => {
            const rawImei = row.getAttribute('data-detail-imei');
            const barang = row.getAttribute('data-nama-barang');
            const kodeOtomatis = row.getAttribute('data-kode-otomatis');
            const tglBeli = row.getAttribute('data-tanggal-beli');
            const tglTerbit = row.getAttribute('data-tanggal-terbit');

            // Cek IMEI (Abaikan jika kosong atau '-')
            if (rawImei && rawImei !== '-' && rawImei !== '') {
                // Pecah jika ada multiple IMEI dalam 1 baris (pisahkan baris baru / koma)
                const listImei = rawImei.split(/[\n,]+/).map(s => s.trim()).filter(s => s !== '');
                listImei.forEach(imei => {
                    if (imei && imei !== '-') {
                        if (!mapImei[imei]) mapImei[imei] = [];
                        mapImei[imei].push({
                            barang,
                            kodeOtomatis,
                            tglBeli,
                            tglTerbit
                        });
                    }
                });
            }
        });

        // Filter Hanya IMEI yang Jumlahnya > 1 (Duplikat)
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
            titleBox.innerText = `Ditemukan ${listDuplikatImei.length} IMEI Duplikat!`;
            descBox.innerText = 'Silakan periksa daftar nomor IMEI yang terinput lebih dari 1 kali di bawah ini:';
            containerRincian.classList.remove('d-none');

            // Render Baris Duplikat IMEI beserta Rincian Tanggal Beli & Terbit
            listDuplikatImei.forEach((item, index) => {
                let detailItemsHtml = item.items.map(it => `
                    <div class="mb-1 pb-1 border-bottom border-light-subtle">
                        <strong>${it.kodeOtomatis}</strong> (${it.barang})
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-calendar-event me-1"></i>Beli: <strong>${it.tglBeli}</strong> | Terbit: <strong>${it.tglTerbit}</strong>
                        </small>
                    </div>
                `).join('');

                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center text-muted fw-semibold">${index + 1}</td>
                    <td class="fw-bold font-monospace text-danger align-middle">${item.val}</td>
                    <td class="text-center align-middle"><span class="badge bg-danger">${item.count}x Muncul</span></td>
                    <td class="text-dark">${detailItemsHtml}</td>
                `;
                bodyTabel.appendChild(tr);
            });
        }
    }
</script>
@endsection