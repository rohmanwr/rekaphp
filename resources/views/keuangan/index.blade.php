@extends('layouts.app')

@section('title', 'Kalkulasi Aset & Progress Keuangan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Progress & Kalkulasi Aset Harian</h3>
        <p class="text-muted small mb-0">Input data harian Anda di sini, dan riwayat progress aset akan terus bertambah secara otomatis.</p>
    </div>
    <!-- Filter Tanggal Input -->
    <form action="{{ route('keuangan.index') }}" method="GET" class="d-flex align-items-center gap-2">
        <label class="small fw-semibold text-muted">Pilih Tanggal:</label>
        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal }}" onchange="this.form.submit()">
    </form>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('keuangan.store') }}" method="POST">
    @csrf
    <input type="hidden" name="tanggal_input" value="{{ $tanggal }}">

    <div class="row g-4 mb-5">
        <!-- Kolom Kiri: Input Form Tempat Aset & Hutang -->
        <div class="col-lg-7">

            <!-- 1. Tempat Aset (Dropdown Bank) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-wallet2 me-2"></i> Tempat Aset (Bank / Dompet)</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="tambahBankBtn"><i class="bi bi-plus-lg"></i> Tambah Bank</button>
                </div>
                <div class="card-body" id="bankContainer">
                    @php
                    $savedBanks = (isset($keuangan) && !empty($keuangan->tempat_aset)) ?$keuangan->tempat_aset : ['Bank Jago' => 0];
                    $bankKeys = array_keys($savedBanks);
                    $bankValues = array_values($savedBanks);
                    @endphp

                    @for($i = 0; $i < count($bankKeys);$i++)
                        @php
                        $bName=$bankKeys[$i];$bNom=$bankValues[$i];
                        @endphp
                        <div class="row g-2 mb-3 bank-row align-items-center">
                        <div class="col-md-5">
                            <select name="bank_nama[]" class="form-select" required>
                                <option value="" disabled>-- Pilih Bank --</option>
                                @for($j = 0; $j < count($daftarBank);$j++)
                                    <option value="{{ $daftarBank[$j] }}" {{ $bName == $daftarBank[$j] ? 'selected' : '' }}>{{ $daftarBank[$j] }}</option>
                                    @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="bank_nominal[]" class="form-control input-rupiah" value="{{ number_format($bNom, 0, ',', '.') }}" placeholder="0" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- 2. Hutang -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-credit-card-2-front me-2"></i> Daftar Hutang (Bulanan / Jangka Panjang)</h5>
                <button type="button" class="btn btn-sm btn-outline-danger" id="tambahHutangBtn"><i class="bi bi-plus-lg"></i> Tambah Hutang</button>
            </div>
            <div class="card-body" id="hutangContainer">
                @php
                $savedHutang = (isset($keuangan) && !empty($keuangan->hutang)) ?$keuangan->hutang : [];
                $hKeys = array_keys($savedHutang);
                $hValues = array_values($savedHutang);
                @endphp

                @if(count($hKeys) > 0)
                @for($k = 0; $k < count($hKeys);$k++)
                    <div class="row g-2 mb-3 hutang-row align-items-center">
                    <div class="col-md-5">
                        <input type="text" name="hutang_nama[]" class="form-control" value="{{ $hKeys[$k] }}" placeholder="Nama Pemberi (Contoh: Ibu)" required>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="hutang_nominal[]" class="form-control input-rupiah" value="{{ number_format($hValues[$k], 0, ',', '.') }}" placeholder="0" required autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus"><i class="bi bi-trash"></i></button>
                    </div>
            </div>
            @endfor
            @else
            <div class="row g-2 mb-3 hutang-row align-items-center">
                <div class="col-md-5">
                    <input type="text" name="hutang_nama[]" class="form-control" placeholder="Nama Pemberi (Contoh: Ibu)">
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" name="hutang_nominal[]" class="form-control input-rupiah" placeholder="0" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="text-end mb-4">
        <button type="submit" class="btn btn-success btn-lg fw-bold px-5 shadow-sm">
            <i class="bi bi-save me-2"></i> Simpan / Update Progress Tanggal Ini
        </button>
    </div>
    </div>

    <!-- Kolom Kanan: Ringkasan Kalkulasi Realtime -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-calculator me-2"></i> Rincian Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h5>
            </div>
            <div class="card-body">
                <!-- Total Profit Dari Histori Penjualan -->
                <div class="mb-3 pb-3 border-bottom">
                    <span class="text-muted small d-block">TOTAL PROFIT (Dari Histori Penjualan)</span>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="fw-semibold text-secondary">{{ $jumlahUnit ?? 0 }} Unit Terjual</span>
                        <span class="fw-bold text-success fs-5">Rp {{ number_format($totalProfitNominal ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Total Tempat Aset -->
                <div class="mb-3 pb-3 border-bottom">
                    <span class="text-muted small d-block">TOTAL TEMPAT ASET (Bank)</span>
                    <span class="fw-bold text-primary fs-5" id="summaryTotalAset">Rp 0</span>
                </div>

                <!-- Total Hutang -->
                <div class="mb-3 pb-3 border-bottom">
                    <span class="text-muted small d-block">TOTAL HUTANG</span>
                    <span class="fw-bold text-danger fs-5" id="summaryTotalHutang">Rp 0</span>
                </div>

                <!-- Rumus Total Bersih Aset -->
                <div class="bg-light p-3 rounded border">
                    <span class="text-dark small fw-bold d-block mb-1">TOTAL BERSIH ASET:</span>
                    <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">(Total Profit + Total Tempat Aset - Total Hutang)</small>
                    <div class="fw-bold text-dark fs-3" id="summaryTotalBersih">Rp 0</div>
                </div>
            </div>
        </div>
    </div>
    </div>
</form>

<!-- TABEL PROGRESS RIWAYAT KEUANGAN HARIAN -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i> Tabel Progress Riwayat Keuangan Harian</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Tanggal Input</th>
                        <th>Rincian Tempat Aset (Bank)</th>
                        <th>Rincian Hutang</th>
                        <th class="text-end">Total Bersih Aset</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($riwayatKeuangan) && count($riwayatKeuangan) > 0)
                    @for($r = 0; $r < count($riwayatKeuangan);$r++)
                        @php $row=$riwayatKeuangan[$r]; @endphp
                        <tr>
                        <td class="text-center fw-semibold text-muted">{{ $r + 1 }}</td>
                        <td class="fw-bold text-dark">
                            {{ \Carbon\Carbon::parse($row->tanggal_input)->translatedFormat('d F Y') }}
                        </td>
                        <td>
                            @if(!empty($row->tempat_aset) && is_array($row->tempat_aset))
                            @foreach($row->tempat_aset as $bankName =>$nomVal)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $bankName }}: Rp {{ number_format($nomVal, 0, ',', '.') }}</span>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($row->hutang) && is_array($row->hutang))
                            @foreach($row->hutang as $pemberiName =>$nomHVal)
                            <span class="badge bg-light text-danger border me-1 mb-1">{{ $pemberiName }}: Rp {{ number_format($nomHVal, 0, ',', '.') }}</span>
                            @endforeach
                            @else
                            <span class="text-muted small">Tidak ada hutang</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-success fs-6">
                            Rp {{ number_format($row->total_bersih_aset, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <a href="{{ route('keuangan.index', ['tanggal' => $row->tanggal_input]) }}" class="btn btn-sm btn-outline-primary" title="Edit / Lihat Tanggal Ini">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </td>
                        </tr>
                        @endfor
                        @else
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat progress keuangan yang disimpan.</td>
                        </tr>
                        @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@php
$optionsHtml = '';
if(!empty($daftarBank)) {
for($bIdx = 0; $bIdx < count($daftarBank);$bIdx++) {
    $bItem=$daftarBank[$bIdx];$optionsHtml .="<option value='{$bItem}'>{$bItem}</option>" ;
    }
    }
    $profitVal=(float)($totalProfitNominal ?? 0);
    @endphp

    <script>
    const listBankOptions = `{!! $optionsHtml !!}`;
    const profitHariIni = {{ $profitVal }};

    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tambahBankBtn').addEventListener('click', function() {
    const container = document.getElementById('bankContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-3 bank-row align-items-center';
    newRow.innerHTML = `
    <div class="col-md-5">
        <select name="bank_nama[]" class="form-select" required>
            <option value="" disabled selected>-- Pilih Bank --</option>
            ${listBankOptions}
        </select>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="bank_nominal[]" class="form-control input-rupiah" placeholder="0" required autocomplete="off">
        </div>
    </div>
    <div class="col-md-1 text-center">
        <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-trash"></i></button>
    </div>
    `;
    container.appendChild(newRow);
    });

    document.getElementById('tambahHutangBtn').addEventListener('click', function() {
    const container = document.getElementById('hutangContainer');
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-3 hutang-row align-items-center';
    newRow.innerHTML = `
    <div class="col-md-5">
        <input type="text" name="hutang_nama[]" class="form-control" placeholder="Nama Pemberi">
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" name="hutang_nominal[]" class="form-control input-rupiah" placeholder="0" autocomplete="off">
        </div>
    </div>
    <div class="col-md-1 text-center">
        <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="bi bi-trash"></i></button>
    </div>
    `;
    container.appendChild(newRow);
    });

    document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-row')) {
    const row = e.target.closest('.row');
    row.remove();
    calculateSummary();
    }
    });

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
    return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function parseRupiah(str) {
    if (!str) return 0;
    return parseFloat(str.replace(/\./g, '')) || 0;
    }

    function calculateSummary() {
    let totalAset = 0;
    document.querySelectorAll('input[name="bank_nominal[]"]').forEach(input => {
    totalAset += parseRupiah(input.value);
    });

    let totalHutang = 0;
    document.querySelectorAll('input[name="hutang_nominal[]"]').forEach(input => {
    totalHutang += parseRupiah(input.value);
    });

    let totalBersih = profitHariIni + totalAset - totalHutang;

    document.getElementById('summaryTotalAset').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalAset);
    document.getElementById('summaryTotalHutang').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHutang);
    document.getElementById('summaryTotalBersih').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalBersih);
    }

    document.addEventListener('keyup', function(e) {
    if (e.target && e.target.classList.contains('input-rupiah')) {
    e.target.value = formatRupiah(e.target.value);
    calculateSummary();
    }
    });

    calculateSummary();
    });
    </script>
    @endsection