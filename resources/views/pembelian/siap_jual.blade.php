@extends('layouts.app')

@section('title', 'Daftar Barang Siap Jual')

@section('content')
<style>
    .ready-mobile-toolbar {
        border: 1px solid #d9e3e1;
        border-radius: 6px;
        background: #fff;
    }

    .ready-item-card {
        border: 1px solid #d9e3e1;
        border-left: 4px solid #18806f;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(26, 46, 42, 0.06);
        overflow: hidden;
    }

    .ready-item-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 0.9rem 0.65rem;
        border-bottom: 1px solid #e8edec;
    }

    .ready-item-select {
        display: inline-flex;
        align-items: flex-start;
        gap: 0.65rem;
        min-width: 0;
        cursor: pointer;
    }

    .ready-item-select .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        flex: 0 0 auto;
        margin-top: 0.1rem;
    }

    .ready-item-name {
        color: #203330;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .ready-item-info {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.7rem;
        padding: 0.8rem 0.9rem;
    }

    .ready-item-info dt {
        margin-bottom: 0.15rem;
        color: #6c757d;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .ready-item-info dd {
        margin-bottom: 0;
        color: #293b38;
        font-size: 0.85rem;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .ready-item-imei {
        margin: 0 0.9rem 0.8rem;
        padding: 0.6rem 0.7rem;
        border: 1px solid #e0e7e5;
        border-radius: 5px;
        background: #f6f8f7;
        color: #384642;
        font-family: monospace;
        font-size: 0.76rem;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .ready-item-actions {
        display: grid;
        grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr);
        gap: 0.55rem;
        padding: 0.75rem 0.9rem;
        border-top: 1px solid #e8edec;
        background: #fbfcfc;
    }

    .ready-item-restore {
        display: flex;
        min-width: 0;
        gap: 0.35rem;
    }

    .ready-item-restore .form-select {
        min-width: 0;
        font-size: 0.78rem;
    }

    @media (max-width: 575.98px) {
        .ready-page-heading h3 {
            font-size: 1.25rem;
        }

        #bulkActionBar {
            padding: 0.55rem !important;
        }

        #bulkActionBar .container {
            align-items: stretch !important;
            padding-right: 0.25rem;
            padding-left: 0.25rem;
        }

        #bulkActionBar .container>div:last-child {
            display: grid !important;
            width: 100%;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0.4rem !important;
        }

        #statusMassalSelect {
            grid-column: 1 / -1;
            width: 100%;
        }

        #bulkActionBar .container>div:last-child>span {
            display: none;
        }

        #bulkActionBar .container>div:last-child button {
            width: 100%;
            padding: 0.4rem;
            font-size: 0.75rem;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="ready-page-heading">
        <h3 class="fw-bold text-dark mb-0">Barang Siap Jual</h3>
        <p class="text-muted small mb-0">Pilih dan centang barang untuk diproses penjualan atau ubah status secara massal.</p>
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

<!-- Searchbar Filter (Live Search Tanpa Enter) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form id="searchForm" action="{{ route('pembelian.siap_jual') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari otomatis berdasarkan Kode, Nama Barang, Toko, atau IMEI..."
                    value="{{ $search ?? '' }}"
                    autocomplete="off"
                    autofocus>
                @if(!empty($search))
                <a href="{{ route('pembelian.siap_jual') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Form Utama untuk Checkbox Massal (Bisa untuk Invoice / Penjualan atau Update Status Massal) -->
<form id="formSiapJual" action="{{ route('invoice.create') }}" method="POST">
    @csrf

    <!-- Tabel Barang Siap Jual (Tanpa Batas Maksimal / Semua Data Tampil) -->
    <div class="card border-0 shadow-sm mb-5 d-none d-xl-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" title="Pilih Semua">
                            </th>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th>No. Pesanan</th>
                            <th>Barang & Toko</th>
                            <th>Detail IMEI</th>
                            <th>Via</th>
                            <th>Tgl Beli</th>
                            <th>Total Modal</th>
                            <th>Harga Jual (Master)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width: 110px;">Aksi</th>
                            <th class="text-center" style="width: 220px;">Kembalikan Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembelians as $index => $item)
                        @php
                        $masterBarang = null;
                        if (isset($barangs)) {
                        if ($barangs instanceof \Illuminate\Support\Collection || is_array($barangs)) {
                        $masterBarang = $barangs[$item->nama_barang] ?? null;
                        }
                        }
                        $hargaJual = $masterBarang ? $masterBarang->harga_jual : 0;
                        @endphp
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="pembelian_ids[]" value="{{ $item->id }}" data-purchase-id="{{ $item->id }}" class="form-check-input item-checkbox item-checkbox-desktop">
                            </td>
                            <td class="text-center fw-semibold text-muted">
                                {{ $index + 1 }}
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ $item->kode_manual ?? '-' }}</span>
                                @if(!empty($item->nama_alamat))
                                <br><small class="text-primary"><i class="bi bi-geo-alt-fill"></i> {{ $item->nama_alamat }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->nama_barang }}</strong><br>
                                <small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
                            </td>
                            <td>
                                @if(!empty($item->detail_imei))
                                <span class="font-monospace text-dark small bg-light p-1 rounded border d-inline-block text-break" style="max-width: 180px; white-space: pre-line;">
                                    {{ $item->detail_imei }}
                                </span>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
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
                            <td class="fw-bold text-secondary">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
                            <td class="fw-bold text-success">
                                @if($hargaJual > 0)
                                Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                @else
                                <span class="text-muted small fst-italic">Belum diatur</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                    <i class="bi bi-rocket-takeoff-fill"></i> Siap Jual
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="submit" formaction="{{ route('invoice.create') }}" name="pembelian_ids[]" value="{{ $item->id }}" class="btn btn-sm btn-primary fw-semibold" title="Proses Penjualan Item Ini">
                                    <i class="bi bi-cash-coin"></i> Jual
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="input-group input-group-sm">
                                    <select id="selectRestore-desktop-{{ $item->id }}" class="form-select form-select-sm bg-light fs-7" title="Pilih Status Pengembalian">
                                        <option value="" disabled selected>Pilih Status...</option>
                                        <option value="Belum Ready">⏳ Belum Ready</option>
                                        <option value="Sudah Ready">✅ Sudah Ready</option>
                                        <option value="Sudah Diambil">📦 Sudah Diambil</option>
                                        <option value="Bermasalah">⚠️ Bermasalah</option>
                                    </select>
                                    <button type="button" data-restore-id="{{ $item->id }}" data-restore-view="desktop" class="btn btn-outline-secondary px-2" title="Eksekusi Pengembalian Status">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada barang yang berstatus siap jual. Silakan ubah status pada menu Rekap Pembelian menjadi "Jual".
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-xl-none mb-5">
        <div class="ready-mobile-toolbar d-flex justify-content-between align-items-center gap-2 p-3 mb-3">
            <label class="d-inline-flex align-items-center gap-2 fw-semibold small mb-0">
                <input type="checkbox" class="form-check-input m-0" id="selectAllMobile">
                Pilih semua barang
            </label>
            <span class="small text-muted"><span id="selectedCountMobile">0</span> dipilih</span>
        </div>

        <div class="d-grid gap-3">
            @forelse($pembelians as $index => $item)
            @php
            $masterBarang = isset($barangs) && ($barangs instanceof \Illuminate\Support\Collection || is_array($barangs))
            ? ($barangs[$item->nama_barang] ?? null)
            : null;
            $hargaJual = $masterBarang ? $masterBarang->harga_jual : 0;
            $badgeColor = match($item->via) {
            'Tokopedia' => 'bg-success',
            'Shopee' => 'bg-warning text-dark',
            'Lazada' => 'bg-primary',
            'TikTok' => 'bg-dark',
            default => 'bg-secondary'
            };
            @endphp
            <article class="ready-item-card">
                <div class="ready-item-top">
                    <label class="ready-item-select">
                        <input type="checkbox" class="form-check-input item-checkbox item-checkbox-mobile" data-purchase-id="{{ $item->id }}" aria-label="Pilih {{ $item->nama_barang }}">
                        <span class="min-w-0">
                            <span class="ready-item-name d-block">{{ $item->nama_barang }}</span>
                            <span class="small text-muted d-block mt-1">{{ $item->kode_manual ?? 'Tanpa nomor pesanan' }}</span>
                        </span>
                    </label>
                    <span class="badge bg-info text-dark flex-shrink-0"><i class="bi bi-rocket-takeoff-fill me-1"></i>Siap Jual</span>
                </div>

                <dl class="ready-item-info mb-0">
                    <div>
                        <dt>Toko</dt>
                        <dd><i class="bi bi-shop me-1 text-muted"></i>{{ $item->nama_toko }}</dd>
                    </div>
                    <div>
                        <dt>Via / Tanggal beli</dt>
                        <dd><span class="badge {{ $badgeColor }} me-1">{{ $item->via }}</span>{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt>Total modal</dt>
                        <dd class="text-secondary">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>Harga jual</dt>
                        <dd class="{{ $hargaJual > 0 ? 'text-success' : 'text-muted' }}">{{ $hargaJual > 0 ? 'Rp ' . number_format($hargaJual, 0, ',', '.') : 'Belum diatur' }}</dd>
                    </div>
                </dl>

                @if(!empty($item->nama_alamat))
                <div class="px-3 pb-2 small text-primary"><i class="bi bi-geo-alt-fill me-1"></i>{{ $item->nama_alamat }}</div>
                @endif
                @if(!empty($item->detail_imei))
                <div class="ready-item-imei"><span class="text-muted d-block mb-1">IMEI / serial</span>{{ $item->detail_imei }}</div>
                @endif

                <div class="ready-item-actions">
                    <button type="submit" formaction="{{ route('invoice.create') }}" name="pembelian_ids[]" value="{{ $item->id }}" class="btn btn-primary fw-semibold">
                        <i class="bi bi-cash-coin me-1"></i>Jual
                    </button>
                    <div class="ready-item-restore">
                        <select id="selectRestore-mobile-{{ $item->id }}" class="form-select form-select-sm bg-white" aria-label="Kembalikan status barang">
                            <option value="" disabled selected>Ubah status</option>
                            <option value="Belum Ready">Belum Ready</option>
                            <option value="Sudah Ready">Sudah Ready</option>
                            <option value="Sudah Diambil">Sudah Diambil</option>
                            <option value="Bermasalah">Bermasalah</option>
                        </select>
                        <button type="button" data-restore-id="{{ $item->id }}" data-restore-view="mobile" class="btn btn-outline-secondary" title="Kembalikan status">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </div>
            </article>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                Belum ada barang siap jual.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Floating Action Bar untuk Aksi Massal (Muncul saat ada item yang dicentang) -->
    <div id="bulkActionBar" class="fixed-bottom bg-dark text-white p-3 shadow-lg d-none" style="z-index: 1050;">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span id="selectedCount" class="fw-bold text-warning">0</span> item dipilih:
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Opsi 1: Ubah Status Massal -->
                <select name="status_massal" id="statusMassalSelect" class="form-select form-select-sm" style="min-width: 170px;">
                    <option value="" disabled selected>-- Ubah Status --</option>
                    <option value="Belum Ready">⏳ Belum Ready</option>
                    <option value="Sudah Ready">✅ Sudah Ready</option>
                    <option value="Sudah Diambil">📦 Sudah Diambil</option>
                    <option value="Bermasalah">⚠️ Bermasalah</option>
                </select>
                <button type="button" id="btnUpdateMassalStatus" class="btn btn-outline-warning btn-sm fw-bold">Update Status</button>

                <span class="text-muted">|</span>

                <!-- Opsi 2: Proses Penjualan Terpilih -->
                <button type="submit" formaction="{{ route('invoice.create') }}" class="btn btn-success btn-sm fw-bold px-3">
                    <i class="bi bi-cart-check-fill me-1"></i> Buat Invoice Terpilih
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Render form restore status satuan terpisah -->
@foreach($pembelians as $item)
<form id="restoreForm-{{ $item->id }}" action="{{ route('pembelian.restoreStatus', $item->id) }}" method="POST" class="d-none">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" id="restoreStatusInput-{{ $item->id }}">
</form>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const selectAllMobile = document.getElementById('selectAllMobile');
        const itemCheckboxes = Array.from(document.querySelectorAll('.item-checkbox'));
        const bulkActionBar = document.getElementById('bulkActionBar');
        const selectedCountSpan = document.getElementById('selectedCount');
        const selectedCountMobile = document.getElementById('selectedCountMobile');
        const btnUpdateMassalStatus = document.getElementById('btnUpdateMassalStatus');
        const statusMassalSelect = document.getElementById('statusMassalSelect');
        const formSiapJual = document.getElementById('formSiapJual');
        const purchaseIds = [...new Set(itemCheckboxes.map(checkbox => checkbox.dataset.purchaseId))];

        function setPurchaseChecked(purchaseId, checked) {
            itemCheckboxes
                .filter(checkbox => checkbox.dataset.purchaseId === purchaseId)
                .forEach(checkbox => checkbox.checked = checked);
        }

        function updateBulkActionState() {
            const selectedIds = new Set(itemCheckboxes
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.dataset.purchaseId));
            const checkedCount = selectedIds.size;
            selectedCountSpan.textContent = checkedCount;
            if (selectedCountMobile) selectedCountMobile.textContent = checkedCount;

            if (checkedCount > 0) {
                bulkActionBar.classList.remove('d-none');
            } else {
                bulkActionBar.classList.add('d-none');
            }

            if (selectAll) selectAll.checked = purchaseIds.length > 0 && checkedCount === purchaseIds.length;
            if (selectAllMobile) selectAllMobile.checked = purchaseIds.length > 0 && checkedCount === purchaseIds.length;
        }

        [selectAll, selectAllMobile].filter(Boolean).forEach(selectAllCheckbox => {
            selectAllCheckbox.addEventListener('change', function() {
                purchaseIds.forEach(purchaseId => setPurchaseChecked(purchaseId, this.checked));
                updateBulkActionState();
            });
        });

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                setPurchaseChecked(this.dataset.purchaseId, this.checked);
                updateBulkActionState();
            });
        });

        document.querySelectorAll('[data-restore-id]').forEach(button => {
            button.addEventListener('click', function() {
                submitRestore(this.dataset.restoreId, this.dataset.restoreView);
            });
        });

        // Handler tombol Update Status Massal
        if (btnUpdateMassalStatus) {
            btnUpdateMassalStatus.addEventListener('click', function() {
                if (!statusMassalSelect.value) {
                    alert('Silakan pilih status baru terlebih dahulu!');
                    return;
                }
                let checkedCount = new Set(itemCheckboxes
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.dataset.purchaseId)).size;
                if (checkedCount === 0) {
                    alert('Pilih minimal satu barang!');
                    return;
                }

                if (confirm('Apakah Anda yakin ingin mengubah status ' + checkedCount + ' barang terpilih secara massal?')) {
                    formSiapJual.action = "{{ route('pembelian.siap_jual.updateStatusMassal') }}";
                    let methodInput = formSiapJual.querySelector('input[name="_method"]');
                    if (!methodInput) {
                        methodInput = document.createElement('input');
                        methodInput.type = 'hidden';
                        methodInput.name = '_method';
                        methodInput.value = 'PATCH';
                        formSiapJual.appendChild(methodInput);
                    } else {
                        methodInput.value = 'PATCH';
                    }
                    formSiapJual.submit();
                }
            });
        }

        // Live Search otomatis tanpa perlu menekan Enter
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timer;

        if (searchInput) {
            // Mempertahankan posisi kursor dan teks di akhir saat halaman mereload data
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;

            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    searchForm.submit();
                }, 500); // Jeda waktu 0.5 detik setelah berhenti mengetik
            });
        }
    });

    function submitRestore(itemId, view) {
        let select = document.getElementById('selectRestore-' + view + '-' + itemId);
        let val = select.value;
        if (!val) {
            alert('Silakan pilih status tujuan terlebih dahulu!');
            return;
        }
        document.getElementById('restoreStatusInput-' + itemId).value = val;
        document.getElementById('restoreForm-' + itemId).submit();
    }
</script>
@endsection