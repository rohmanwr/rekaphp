@extends('layouts.app')

@section('title', 'Rekap Pembelian HP')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Rekap Pembelian</h3>
    <a href="{{ route('pembelian.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Pembelian
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Searchbar Filter & Filter Status -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form id="searchForm" action="{{ route('pembelian.index') }}" method="GET">
            @if(!empty($selectedStatus))
            <input type="hidden" name="status" value="{{ $selectedStatus }}">
            @endif
            <div class="input-group mb-3">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari otomatis berdasarkan Kode TRX, Nama Barang, Device, atau Toko..."
                    value="{{ $search }}"
                    autocomplete="off"
                    autofocus>
                @if(!empty($search))
                <a href="{{ route('pembelian.index', ['status' => $selectedStatus]) }}" class="btn btn-outline-secondary" title="Reset Pencarian Teks">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>

        <!-- Filter Tombol Status -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold text-muted small me-1"><i class="bi bi-funnel-fill"></i> Filter Status:</span>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search])) }}"
                class="btn btn-sm rounded-pill {{ empty($selectedStatus) ? 'btn-dark' : 'btn-outline-secondary' }}">
                Semua
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Belum Ready'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Belum Ready' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">
                ⏳ Belum Ready
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Ready'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Sudah Ready' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                ✅ Sudah Ready
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Sudah Diambil'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Sudah Diambil' ? 'btn-primary fw-bold' : 'btn-outline-primary' }}">
                📦 Sudah Diambil
            </a>

            <a href="{{ route('pembelian.index', array_filter(['search' => $search, 'status' => 'Bermasalah'])) }}"
                class="btn btn-sm rounded-pill {{ $selectedStatus == 'Bermasalah' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                ⚠️ Bermasalah
            </a>
        </div>
    </div>
</div>

<!-- Tabel Rekap Pembelian -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode Transaksi</th>
                        <th>Barang & Toko</th>
                        <th>Via</th>
                        <th style="width: 150px;">Status</th>
                        <th>Tgl Beli</th>
                        <th>Total Modal</th>
                        <th>Lampiran</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $index => $item)
                    <tr>
                        <td class="text-center fw-semibold text-muted">
                            {{ $pembelians->firstItem() ? $pembelians->firstItem() + $index : $index + 1 }}
                        </td>
                        <td>{{ $item->kode_manual ?? '-' }}</td>
                        <td>
                            <strong>{{ $item->nama_barang }}</strong>
                            @if(!empty($item->nama_device))
                            <br><small class="text-primary fw-semibold"><i class="bi bi-phone"></i> {{ $item->nama_device }}</small>
                            @endif
                            <br><small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
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
                        <td>
                            <!-- Quick Update Status (Samping Kanan Via) -->
                            <form action="{{ route('pembelian.updateStatus', $item->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PATCH')
                                @php
                                $statusClass = match($item->status) {
                                'Sudah Ready' => 'btn-outline-success',
                                'Belum Ready' => 'btn-outline-warning text-dark',
                                'Sudah Diambil' => 'btn-outline-primary',
                                'Bermasalah' => 'btn-outline-danger',
                                default => 'btn-outline-secondary'
                                };
                                @endphp
                                <select name="status" class="form-select form-select-sm rounded-pill fw-semibold {{ $statusClass }}" onchange="this.form.submit()" style="width: fit-content; min-width: 140px;">
                                    <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                                    <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                                    <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                                    <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                                </select>
                            </form>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</td>
                        <td class="fw-bold">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
                        <td>
                            @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($item->file_lampiran as $idx => $filePath)
                                @php $ext = pathinfo($filePath, PATHINFO_EXTENSION); @endphp
                                <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="btn btn-xs btn-outline-info p-1 px-2 text-decoration-none" style="font-size: 0.75rem;">
                                    <i class="bi {{ strtolower($ext) == 'pdf' ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-primary' }}"></i>
                                    File {{ $idx + 1 }}
                                </a>
                                @endforeach
                            </div>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditPembelian{{ $item->id }}" title="Edit Data">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('pembelian.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data rekap pembelian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold" title="Hapus Transaksi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Pembelian -->
                    <div class="modal fade" id="modalEditPembelian{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Rekap Pembelian ({{ $item->kode_otomatis }})</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('pembelian.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="form-pembelian">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Kode Transaksi</label>
                                                <input type="text" name="kode_manual" class="form-control" value="{{ old('kode_manual', $item->kode_manual) }}" placeholder="No. Invoice / Resi Toko">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Nama Barang Pembelian</label>
                                                <select name="nama_barang" class="form-select" required>
                                                    <option value="" disabled>-- Pilih Barang --</option>
                                                    @foreach($barangs as $brg)
                                                    <option value="{{ $brg->nama_barang }}" {{ $item->nama_barang == $brg->nama_barang ? 'selected' : '' }}>
                                                        {{ $brg->nama_barang }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Nama Toko Pembelian</label>
                                                <select name="nama_toko" class="form-select" required>
                                                    <option value="" disabled>-- Pilih Toko --</option>
                                                    @foreach($tokos as $tk)
                                                    <option value="{{ $tk->nama_toko }}" {{ $item->nama_toko == $tk->nama_toko ? 'selected' : '' }}>
                                                        {{ $tk->nama_toko }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @php
                                            $isCustomVia = !in_array($item->via, ['Tokopedia', 'Shopee', 'Lazada', 'TikTok']);
                                            @endphp
                                            <div class="col-md-6">
                                                <label class="form-label">Transaksi Beli Via</label>
                                                <select id="selectViaEdit{{ $item->id }}" class="form-select select-via-toggle" data-target="#inputViaEdit{{ $item->id }}" required>
                                                    <option value="Tokopedia" {{ $item->via == 'Tokopedia' ? 'selected' : '' }}>Tokopedia</option>
                                                    <option value="Shopee" {{ $item->via == 'Shopee' ? 'selected' : '' }}>Shopee</option>
                                                    <option value="Lazada" {{ $item->via == 'Lazada' ? 'selected' : '' }}>Lazada</option>
                                                    <option value="TikTok" {{ $item->via == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                                    <option value="Lainnya" {{ $isCustomVia ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                                                </select>
                                                <input
                                                    type="text"
                                                    id="inputViaEdit{{ $item->id }}"
                                                    name="via"
                                                    class="form-control mt-2 {{ $isCustomVia ? '' : 'd-none' }}"
                                                    value="{{ $item->via }}"
                                                    placeholder="Ketik platform/via transaksi manual..."
                                                    {{ $isCustomVia ? 'required' : '' }}>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Status Barang</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="Belum Ready" {{ $item->status == 'Belum Ready' ? 'selected' : '' }}>⏳ Belum Ready</option>
                                                    <option value="Sudah Ready" {{ $item->status == 'Sudah Ready' ? 'selected' : '' }}>✅ Sudah Ready</option>
                                                    <option value="Sudah Diambil" {{ $item->status == 'Sudah Diambil' ? 'selected' : '' }}>📦 Sudah Diambil</option>
                                                    <option value="Bermasalah" {{ $item->status == 'Bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Tanggal Beli</label>
                                                <input type="date" name="tanggal_beli" class="form-control" value="{{ old('tanggal_beli', $item->tanggal_beli) }}" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Total Modal (Rp)</label>
                                                <input
                                                    type="text"
                                                    name="total_modal"
                                                    class="form-control input-rupiah"
                                                    value="{{ number_format($item->total_modal, 0, ',', '.') }}"
                                                    placeholder="Misal: 8.500.000"
                                                    required
                                                    autocomplete="off">
                                            </div>

                                            @if(!empty($item->file_lampiran) && count($item->file_lampiran) > 0)
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Lampiran Ter-upload (Centang untuk menghapus saat update):</label>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($item->file_lampiran as $idx => $filePath)
                                                    <div class="border rounded p-2 bg-light d-flex flex-column align-items-start gap-1" style="min-width: 120px;">
                                                        <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="small text-decoration-none fw-semibold text-truncate w-100" title="File {{ $idx + 1 }}">
                                                            <i class="bi bi-paperclip"></i> File {{ $idx + 1 }}
                                                        </a>
                                                        <div class="form-check form-check-inline m-0 pt-1 border-top w-100">
                                                            <input class="form-check-input bg-danger border-danger" type="checkbox" name="delete_files[]" value="{{ $idx }}" id="delFile{{ $item->id }}_{{ $idx }}">
                                                            <label class="form-check-label small text-danger fw-semibold" for="delFile{{ $item->id }}_{{ $idx }}" style="font-size: 0.75rem;">
                                                                Hapus File
                                                            </label>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif

                                            <div class="col-md-6">
                                                <label class="form-label">Tambah Lampiran Baru (Bisa Banyak)</label>
                                                <input type="file" name="file_lampiran[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                                                <small class="text-muted fs-7">Bisa pilih lebih dari 1 file (JPG, PNG, PDF maks 2MB/file)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-white fw-semibold">Update Pembelian</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada data rekap pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pembelians->hasPages())
    <div class="card-footer bg-white">
        {{ $pembelians->appends(['search' => $search, 'status' => $selectedStatus])->links() }}
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        let timer;

        if (searchInput) {
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;

            searchInput.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    searchForm.submit();
                }, 500);
            });
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('select-via-toggle')) {
                const selectElement = e.target;
                const targetInput = document.querySelector(selectElement.dataset.target);

                if (selectElement.value === 'Lainnya') {
                    targetInput.classList.remove('d-none');
                    targetInput.value = '';
                    targetInput.focus();
                    targetInput.required = true;
                } else {
                    targetInput.classList.add('d-none');
                    targetInput.value = selectElement.value;
                    targetInput.required = false;
                }
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

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        document.addEventListener('keyup', function(e) {
            if (e.target && e.target.classList.contains('input-rupiah')) {
                e.target.value = formatRupiah(e.target.value);
            }
        });

        document.querySelectorAll('.form-pembelian').forEach(function(form) {
            form.addEventListener('submit', function() {
                let rupiahInput = form.querySelector('.input-rupiah');
                if (rupiahInput) {
                    rupiahInput.value = rupiahInput.value.replace(/\./g, '');
                }
            });
        });
    });
</script>
@endsection