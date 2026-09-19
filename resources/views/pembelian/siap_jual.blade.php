@extends('layouts.app')

@section('title', 'Daftar Barang Siap Jual')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-0">Barang Siap Jual</h3>
        <p class="text-muted small mb-0">Pilih dan centang barang yang ingin diproses penjualannya hari ini.</p>
    </div>

    <!-- Tombol Aksi Massal (Muncul otomatis jika ada checkbox yang dicentang) -->
    <div id="bulkActionContainer" class="d-none">
        <button type="button" class="btn btn-success fw-semibold shadow-sm" id="btnProsesTerpilih">
            <i class="bi bi-cart-check-fill me-1"></i> Proses Penjualan Terpilih (<span id="selectedCount">0</span>)
        </button>
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
        <form action="{{ route('penjualan.siap-jual') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan Kode, Nama Barang, Toko, atau IMEI..."
                    value="{{ $search ?? '' }}"
                    autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('penjualan.siap-jual') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Form Khusus untuk Proses Checkbox Massal -->
<form id="formSiapJual" action="{{ route('invoice.create') }}" method="POST">
    @csrf
    <!-- Tabel Barang Siap Jual -->
    <div class="card border-0 shadow-sm">
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
                        // Ambil harga jual dari master barang berdasarkan nama_barang
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
                                <!-- Checkbox per item -->
                                <input type="checkbox" name="pembelian_ids[]" value="{{ $item->id }}" class="form-check-input item-checkbox">
                            </td>
                            <td class="text-center fw-semibold text-muted">
                                {{ method_exists($pembelians, 'firstItem') && $pembelians->firstItem() ? $pembelians->firstItem() + $index : $index + 1 }}
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
                            <!-- Kolom Tombol Jual Satuan -->
                            <td class="text-center">
                                <button type="submit" formaction="{{ route('invoice.create') }}" name="pembelian_ids[]" value="{{ $item->id }}" class="btn btn-sm btn-primary fw-semibold" title="Proses Penjualan Item Ini">
                                    <i class="bi bi-cash-coin"></i> Jual
                                </button>
                            </td>
                            <!-- Kolom Kembalikan Status -->
                            <td class="text-center">
                                <div class="input-group input-group-sm">
                                    <select id="selectRestore-{{ $item->id }}" class="form-select form-select-sm bg-light fs-7" title="Pilih Status Pengembalian">
                                        <option value="" disabled selected>Pilih Status...</option>
                                        <option value="Belum Ready">⏳ Belum Ready</option>
                                        <option value="Sudah Ready">✅ Sudah Ready</option>
                                        <option value="Sudah Diambil">📦 Sudah Diambil</option>
                                        <option value="Bermasalah">⚠️ Bermasalah</option>
                                    </select>
                                    <button type="button" onclick="submitRestore({{ $item->id }})" class="btn btn-outline-secondary px-2" title="Eksekusi Pengembalian Status">
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

        <!-- Paginasi Aman -->
        @if(method_exists($pembelians, 'hasPages') && $pembelians->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $pembelians->appends(['search' => $search ?? ''])->links() }}
        </div>
        @endif
    </div>
</form>

<!-- Render form restore status terpisah di luar form utama agar tidak konflik method PATCH -->
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
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const bulkActionContainer = document.getElementById('bulkActionContainer');
        const selectedCountSpan = document.getElementById('selectedCount');
        const btnProsesTerpilih = document.getElementById('btnProsesTerpilih');

        function updateBulkActionState() {
            let checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            selectedCountSpan.textContent = checkedCount;

            if (checkedCount > 0) {
                bulkActionContainer.classList.remove('d-none');
            } else {
                bulkActionContainer.classList.add('d-none');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                itemCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkActionState();
            });
        }

        itemCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateBulkActionState();
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }
            });
        });

        if (btnProsesTerpilih) {
            btnProsesTerpilih.addEventListener('click', function() {
                let form = document.getElementById('formSiapJual');
                form.action = "{{ route('invoice.create') }}";
                form.method = "POST";
                form.submit();
            });
        }
    });

    // Fungsi aman untuk mentrigger form pengembalian status tanpa merusak form massal
    function submitRestore(itemId) {
        let select = document.getElementById('selectRestore-' + itemId);
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