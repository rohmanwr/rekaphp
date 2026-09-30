@extends('layouts.app')

@section('title', 'Kalkulasi Aset & Progress Keuangan')

@section('content')
<style>
    .keuangan-page {
        --keuangan-teal: #176b62;
        --keuangan-ink: #173b3d;
        --keuangan-line: #e4e9e8;
    }

    .keuangan-heading {
        gap: 1rem;
        padding: 1rem 1.15rem;
        background: #fff;
        border: 1px solid var(--keuangan-line);
        border-left: 4px solid var(--keuangan-teal);
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(23, 59, 61, 0.04);
    }

    .keuangan-heading h3 {
        color: var(--keuangan-ink) !important;
        font-size: 1.35rem;
    }

    .keuangan-date-filter {
        flex-shrink: 0;
        padding: .5rem .65rem;
        background: #f5f8f7;
        border: 1px solid var(--keuangan-line);
        border-radius: 8px;
    }

    .keuangan-page .card {
        min-width: 0;
        border-radius: 10px;
    }

    .keuangan-page .card-header {
        gap: .75rem;
    }

    .keuangan-page .card-header h5 {
        min-width: 0;
        line-height: 1.35;
    }

    .keuangan-input-column,
    .keuangan-summary-column {
        min-width: 0;
    }

    .summary-card {
        top: 1rem;
    }

    .net-asset-result {
        color: #fff;
        background: var(--keuangan-ink);
        border: 0 !important;
    }

    .net-asset-result .text-dark,
    .net-asset-result .text-muted {
        color: rgba(255, 255, 255, .76) !important;
    }

    .history-table {
        min-width: 900px;
    }

    @media (max-width: 575.98px) {
        .keuangan-heading {
            align-items: stretch !important;
            flex-direction: column;
            padding: 1rem;
            margin-bottom: 1rem !important;
        }

        .keuangan-heading h3 {
            font-size: 1.2rem;
        }

        .keuangan-date-filter {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: .35rem !important;
            width: 100%;
        }

        .keuangan-date-filter input {
            width: 100%;
            min-height: 42px;
        }

        .keuangan-page .row.g-4 {
            --bs-gutter-y: 1rem;
            --bs-gutter-x: .75rem;
        }

        .keuangan-page .card-header {
            align-items: flex-start !important;
            flex-wrap: wrap;
            padding: .85rem 1rem !important;
        }

        .keuangan-page .card-header h5 {
            font-size: 1rem;
        }

        .keuangan-page .card-body {
            padding: 1rem;
        }

        #cardAsetHp .card-header>div {
            width: 100%;
            justify-content: space-between;
        }

        #cardAsetHp .card-body>.d-flex {
            align-items: flex-start !important;
            flex-direction: column;
            gap: .5rem;
        }

        #displayModalAsetHp {
            font-size: 1.35rem !important;
            overflow-wrap: anywhere;
        }

        .keuangan-page .bank-row,
        .keuangan-page .hutang-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 42px;
            gap: .5rem;
            align-items: center !important;
            margin: 0 0 1rem !important;
        }

        .keuangan-page .bank-row>*,
        .keuangan-page .hutang-row>* {
            width: auto;
            max-width: none;
            padding-right: 0;
            padding-left: 0;
        }

        .keuangan-page .bank-row>.col-md-5,
        .keuangan-page .hutang-row>.col-md-5 {
            grid-column: 1 / -1;
        }

        .keuangan-page .bank-row>.col-md-6,
        .keuangan-page .hutang-row>.col-md-6 {
            grid-column: 1;
        }

        .keuangan-page .bank-row>.col-md-1,
        .keuangan-page .hutang-row>.col-md-1 {
            grid-column: 2;
            grid-row: 2;
        }

        .keuangan-page .remove-row {
            width: 42px;
            min-height: 42px;
        }

        .keuangan-page .input-group-text,
        .keuangan-page .form-control,
        .keuangan-page .form-select {
            min-height: 42px;
        }

        .summary-card {
            position: static !important;
        }

        .net-asset-result #summaryTotalBersih {
            font-size: 1.65rem !important;
            overflow-wrap: anywhere;
        }

        .save-progress-btn {
            width: 100%;
            padding: .8rem 1rem !important;
            font-size: .95rem;
        }

        .keuangan-page .table-responsive {
            margin-bottom: 0;
        }

        .history-table {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .history-table thead {
            display: none;
        }

        .history-table tbody {
            display: grid;
            gap: .75rem;
            padding: .75rem;
        }

        .history-table tbody tr {
            display: block;
            padding: .65rem .85rem;
            background: #fff;
            border: 1px solid var(--keuangan-line);
            border-left: 3px solid var(--keuangan-teal);
            border-radius: 9px;
            box-shadow: 0 2px 8px rgba(23, 59, 61, .05);
        }

        .history-table tbody td {
            display: block;
            padding: .55rem 0 !important;
            text-align: left !important;
            border-bottom: 1px solid #edf0ef;
            overflow-wrap: anywhere;
        }

        .history-table tbody td[data-label]::before {
            display: block;
            margin-bottom: .3rem;
            color: #6c757d;
            content: attr(data-label);
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .history-table .history-index {
            display: none;
        }

        .history-table .history-date {
            color: var(--keuangan-ink) !important;
            font-size: 1rem;
        }

        .history-table tbody td.history-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }

        .history-table td.history-total::before {
            flex: 0 0 auto;
            margin-bottom: 0;
        }

        .history-table .history-amount {
            text-align: right;
            overflow-wrap: anywhere;
        }

        .history-table tbody td:last-child {
            padding-bottom: 0 !important;
            border-bottom: 0;
        }

        .history-table .history-edit {
            width: 100%;
            min-height: 42px;
        }

        .history-table tbody td[colspan] {
            padding: 1rem !important;
            text-align: center !important;
            border-bottom: 0;
        }
    }
</style>

<div class="keuangan-page">
    <div class="keuangan-heading d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Progress & Kalkulasi Aset Harian</h3>
            <p class="text-muted small mb-0">Input data harian Anda di sini, dan riwayat progress aset akan terus bertambah secara otomatis.</p>
        </div>
        <!-- Filter Tanggal Input -->
        <form action="{{ route('keuangan.index') }}" method="GET" class="keuangan-date-filter d-flex align-items-center gap-2">
            <label class="small fw-semibold text-muted">Pilih Tanggal:</label>
            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal }}" onchange="this.form.submit()">
        </form>
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('keuangan.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal_input" value="{{ $tanggal }}">
        <input type="hidden" id="isAsetHpDeletedInput" name="is_aset_hp_deleted" value="0">

        <div class="row g-4 mb-5">
            <!-- Kolom Kiri: Input Form Aset & Hutang -->
            <div class="col-lg-7 keuangan-input-column order-2 order-lg-1">

                <!-- 1. Aset Handphone -->
                <div class="card border-0 shadow-sm mb-4 bg-light border-start border-4 border-info position-relative" id="cardAsetHp">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-info"><i class="bi bi-phone me-2"></i> Aset Handphone (Status: Sudah Diambil)</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info text-dark fw-semibold px-3 py-2 fs-6" id="badgeUnitAsetHp">{{ $totalUnitAsetHp ?? 0 }} Unit Ready</span>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteAsetHp" title="Hapus Aset HP (Ubah Nilai Jadi 0)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block">Total Nilai Modal HP Ready:</span>
                                <small class="text-muted" style="font-size: 0.75rem;">Diambil otomatis dari Rekap Pembelian status "Sudah Diambil"</small>
                            </div>
                            <div class="fw-bold text-info fs-4" id="displayModalAsetHp">
                                Rp {{ number_format($totalModalAsetHp ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Tempat Aset (Dropdown & Input Manual "Lainnya") -->
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
                        $daftarPreset =$daftarBank ?? ['Bank Jago', 'Bank BCA', 'Bank UOB', 'Bank Mandiri', 'Bank BNI', 'BSI', 'SeaBank', 'Blu by BCA', 'GoPay / DANA / OVO'];
                        @endphp

                        @for ($i = 0; $i < count($bankKeys);$i++)
                            @php
                            $bName=$bankKeys[$i];
                            $bNom=$bankValues[$i];$isPreset=in_array($bName,$daftarPreset);
                            @endphp
                            <div class="row g-2 mb-3 bank-row align-items-center">
                            <div class="col-md-5">
                                <select class="form-select bank-select" onchange="checkManualBank(this)" required>
                                    <option value="" disabled>-- Pilih Bank --</option>
                                    @foreach ($daftarPreset as $bankOption)
                                    <option value="{{ $bankOption }}" {{ $isPreset &&$bName == $bankOption ? 'selected' : '' }}>{{ $bankOption }}</option>
                                    @endforeach
                                    <option value="MANUAL" {{ !$isPreset ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                                </select>

                                <input type="text" class="form-control mt-2 bank-manual-input {{ $isPreset ? 'd-none' : '' }}"
                                    value="{{ $bName }}" placeholder="Ketik Manual..." {{ !$isPreset ? 'required' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="bank_nominal[]" class="form-control input-rupiah" value="{{ number_format($bNom, 0, ',', '.') }}" placeholder="0" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-1 text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus Baris Ini"><i class="bi bi-trash"></i></button>
                            </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- 3. Daftar Hutang -->
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

                    @if (count($hKeys) > 0)
                    @for ($k = 0; $k < count($hKeys);$k++)
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
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus Baris Ini"><i class="bi bi-trash"></i></button>
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
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus Baris Ini"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-success btn-lg fw-bold px-5 shadow-sm save-progress-btn">
                <i class="bi bi-save me-2"></i> Simpan / Update Progress Tanggal Ini
            </button>
        </div>
</div>

<!-- Kolom Kanan: Ringkasan Kalkulasi Realtime -->
<div class="col-lg-5 keuangan-summary-column order-1 order-lg-2">
    <div class="card border-0 shadow-sm sticky-top summary-card" style="top: 20px;">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-calculator me-2"></i> Rincian Tanggal: {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</h5>
        </div>
        <div class="card-body">
            <!-- Total Profit Dari Histori Penjualan -->
            <div class="mb-3 pb-3 border-bottom">
                <span class="text-muted small d-block fw-semibold">TOTAL PROFIT (Dari Histori Penjualan)</span>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="fw-semibold text-secondary">{{ $jumlahUnit ?? 0 }} Unit Terjual</span>
                    <span class="fw-bold text-success fs-5">Rp {{ number_format($totalProfitNominal ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Total Aset HP (Status: Sudah Diambil) -->
            <div class="mb-3 pb-3 border-bottom">
                <span class="text-muted small d-block fw-semibold"><i class="bi bi-phone me-1"></i> ASET HANDPHONE (Status: Sudah Diambil)</span>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="fw-semibold text-secondary" id="summaryUnitAsetHp">{{ $totalUnitAsetHp ?? 0 }} Unit Total</span>
                    <span class="fw-bold text-info fs-5" id="summaryModalAsetHp">Rp {{ number_format($totalModalAsetHp ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Total Tempat Aset -->
            <div class="mb-3 pb-3 border-bottom">
                <span class="text-muted small d-block fw-semibold">TOTAL TEMPAT ASET (Bank)</span>
                <span class="fw-bold text-primary fs-5" id="summaryTotalAset">Rp 0</span>
            </div>

            <!-- Total Hutang -->
            <div class="mb-3 pb-3 border-bottom">
                <span class="text-muted small d-block fw-semibold">TOTAL HUTANG</span>
                <span class="fw-bold text-danger fs-5" id="summaryTotalHutang">Rp 0</span>
            </div>

            <!-- Rumus Total Bersih Aset -->
            <div class="net-asset-result p-3 rounded-3">
                <span class="text-dark small fw-bold d-block mb-1">TOTAL BERSIH ASET:</span>
                <small class="text-muted d-block mb-2" style="font-size: 0.72rem;">(Modal Aset HP + Bank - Hutang)</small>
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
            <table class="table table-hover align-middle mb-0 history-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Tanggal Input</th>
                        <th>Rincian Tempat Aset (Bank)</th>
                        <th>Rincian Hutang</th>
                        <th class="text-end">Growth Profit</th>
                        <th class="text-end">Total Bersih Aset</th>
                        <th class="text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($riwayatKeuangan) && count($riwayatKeuangan) > 0)
                    @for ($r = 0; $r < count($riwayatKeuangan);$r++)
                        @php $row=$riwayatKeuangan[$r]; @endphp
                        <tr>
                        <td class="text-center fw-semibold text-muted history-index" data-label="No">{{ $r + 1 }}</td>
                        <td class="fw-bold text-dark history-date" data-label="Tanggal">
                            {{ \Carbon\Carbon::parse($row->tanggal_input)->translatedFormat('d F Y') }}
                        </td>
                        <td data-label="Bank / Dompet">
                            @if (!empty($row->tempat_aset) && is_array($row->tempat_aset))
                            @foreach ($row->tempat_aset as $bankName => $nomVal)
                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $bankName }}: Rp {{ number_format($nomVal, 0, ',', '.') }}</span>
                            @endforeach
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td data-label="Hutang">
                            @if (!empty($row->hutang) && is_array($row->hutang))
                            @foreach ($row->hutang as $pemberiName => $nomHVal)
                            <span class="badge bg-light text-danger border me-1 mb-1">{{ $pemberiName }}: Rp {{ number_format($nomHVal, 0, ',', '.') }}</span>
                            @endforeach
                            @else
                            <span class="text-muted small">Tidak ada hutang</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $row->total_profit_nominal === null ? 'text-muted' : ($row->total_profit_nominal >= 0 ? 'text-success' : 'text-danger') }} history-profit" data-label="Growth Profit">
                            <span class="history-amount">
                                @if ($row->total_profit_nominal === null)
                                -
                                @elseif ($row->total_profit_nominal < 0)
                                    -Rp {{ number_format(abs($row->total_profit_nominal), 0, ',', '.') }}
                                    @else
                                    Rp {{ number_format($row->total_profit_nominal, 0, ',', '.') }}
                                    @endif
                                    </span>
                        </td>
                        <td class="text-end fw-bold text-success fs-6 history-total" data-label="Total Bersih Aset">
                            <span class="history-amount">Rp {{ number_format($row->total_bersih_aset, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center" data-label="Aksi">
                            <a href="{{ route('keuangan.index', ['tanggal' => $row->tanggal_input]) }}" class="btn btn-sm btn-outline-primary history-edit" title="Edit / Lihat Tanggal Ini">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </td>
                        </tr>
                        @endfor
                        @else
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat progress keuangan yang disimpan.</td>
                        </tr>
                        @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

@php
$optionsHtml = '';
if (!empty($daftarBank)) {
foreach ($daftarBank as $bItem) {$optionsHtml .= "<option value='" . addslashes($bItem) . "'>" . htmlspecialchars($bItem) . "</option>";
}
}
$modalAsetHpVal = (float)($totalModalAsetHp ?? 0);
@endphp

<div id="keuanganData"
    data-bank-options='@json($optionsHtml)'
    data-modal-aset-hp="{{ $modalAsetHpVal }}"
    hidden></div>

<script>
    const keuanganData = document.getElementById('keuanganData');
    const listBankOptions = JSON.parse(keuanganData.dataset.bankOptions);
    let currentModalAsetHp = Number(keuanganData.dataset.modalAsetHp);

    // Fungsi Kontrol Dropdown & Input Manual
    function checkManualBank(selectElem) {
        const parentCol = selectElem.closest('.col-md-5');
        const manualInput = parentCol.querySelector('.bank-manual-input');

        if (selectElem.value === 'MANUAL') {
            manualInput.classList.remove('d-none');
            manualInput.setAttribute('required', 'required');
            manualInput.setAttribute('name', 'bank_nama[]');
            selectElem.removeAttribute('name');
            manualInput.focus();
        } else {
            manualInput.classList.add('d-none');
            manualInput.removeAttribute('required');
            manualInput.removeAttribute('name');
            selectElem.setAttribute('name', 'bank_nama[]');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi awal nama attribute saat halaman dimuat
        document.querySelectorAll('.bank-row').forEach(row => {
            const select = row.querySelector('.bank-select');
            const manualInput = row.querySelector('.bank-manual-input');
            if (select && select.value === 'MANUAL') {
                manualInput.setAttribute('name', 'bank_nama[]');
                select.removeAttribute('name');
            } else if (select) {
                select.setAttribute('name', 'bank_nama[]');
                manualInput.removeAttribute('name');
            }
        });

        // Handler Tombol Delete Aset HP
        const btnDeleteAsetHp = document.getElementById('btnDeleteAsetHp');
        if (btnDeleteAsetHp) {
            btnDeleteAsetHp.addEventListener('click', function() {
                if (confirm('Apakah Anda yakin ingin menghapus nilai Aset HP pada kalkulasi ini? Nilainya akan diubah menjadi Rp 0.')) {
                    currentModalAsetHp = 0;
                    document.getElementById('isAsetHpDeletedInput').value = "1";

                    document.getElementById('displayModalAsetHp').textContent = 'Rp 0';
                    document.getElementById('summaryModalAsetHp').textContent = 'Rp 0';
                    document.getElementById('badgeUnitAsetHp').textContent = '0 Unit Ready';
                    document.getElementById('summaryUnitAsetHp').textContent = '0 Unit Total';

                    btnDeleteAsetHp.disabled = true;
                    btnDeleteAsetHp.classList.replace('btn-outline-danger', 'btn-secondary');

                    calculateSummary();
                }
            });
        }

        // Tombol Tambah Bank
        document.getElementById('tambahBankBtn').addEventListener('click', function() {
            const container = document.getElementById('bankContainer');
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mb-3 bank-row align-items-center';
            newRow.innerHTML = `
            <div class="col-md-5">
                <select name="bank_nama[]" class="form-select bank-select" onchange="checkManualBank(this)" required>
                    <option value="" disabled selected>-- Pilih Bank --</option>
                    ${listBankOptions}
                    <option value="MANUAL">Lainnya (Ketik Manual)</option>
                </select>
                <input type="text" class="form-control mt-2 bank-manual-input d-none" placeholder="Ketik Manual...">
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="bank_nominal[]" class="form-control input-rupiah" placeholder="0" required autocomplete="off">
                </div>
            </div>
            <div class="col-md-1 text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus Baris Ini"><i class="bi bi-trash"></i></button>
            </div>
            `;
            container.appendChild(newRow);
        });

        // Tombol Tambah Hutang
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
                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Hapus Baris Ini"><i class="bi bi-trash"></i></button>
            </div>
            `;
            container.appendChild(newRow);
        });

        // Event Listener Hapus Baris
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

            let totalBersih = currentModalAsetHp + totalAset - totalHutang;

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