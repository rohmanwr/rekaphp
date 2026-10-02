@extends('layouts.app')

@section('title', 'Master Data Toko')

@section('content')
<style>
    .toko-table td,
    .toko-table th {
        vertical-align: middle;
    }

    .toko-table td {
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .toko-add-button {
            width: 100%;
        }

        .toko-table {
            display: block;
            width: 100%;
        }

        .toko-table thead {
            display: none;
        }

        .toko-table tbody {
            display: block;
            padding: .5rem;
        }

        .toko-table tbody tr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .5rem;
            padding: .35rem .55rem;
            border: 1px solid #adb5bd;
            border-radius: .5rem;
            background: #fff;
        }

        .toko-table tbody td {
            display: block;
            padding: .3rem;
            border: 0;
            text-align: left;
        }

        .toko-table tbody td[data-label="No"] {
            display: none;
        }

        .toko-table tbody td[data-label="Nama Toko"] {
            flex: 1;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .toko-table tbody td[colspan] {
            display: block;
            text-align: center;
        }

        .toko-table .store-actions {
            flex-wrap: nowrap;
        }

        .toko-table .store-actions .btn {
            min-width: 38px;
            min-height: 38px;
        }
    }
</style>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <h3 class="fw-bold text-dark mb-0">Master Data Toko</h3>
    <button class="btn btn-primary toko-add-button" data-bs-toggle="modal" data-bs-target="#modalTambahToko">
        <i class="bi bi-plus-lg"></i> Tambah Toko
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Searchbar Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 p-sm-3">
        <form id="searchForm" action="{{ route('toko.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan nama toko atau lokasi..."
                    value="{{ $search }}"
                    autocomplete="off">
                @if(!empty($search))
                <a href="{{ route('toko.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Data Toko -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle mb-0 toko-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Toko</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokos as $index => $item)
                    <tr>
                        <td class="text-center fw-semibold text-muted" data-label="No">
                            {{ $loop->iteration }}
                        </td>
                        <td class="fw-semibold" data-label="Nama Toko">{{ $item->nama_toko }}</td>
                        <td class="text-center" data-label="Aksi">
                            <div class="store-actions d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditToko{{ $item->id }}" title="Edit Data" aria-label="Edit toko {{ $item->nama_toko }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('toko.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus toko ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger text-white fw-semibold" title="Hapus Data" aria-label="Hapus toko {{ $item->nama_toko }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Toko -->
                    <div class="modal fade" id="modalEditToko{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Data Toko</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('toko.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Kode Toko <span class="text-muted fw-normal">(Opsional)</span></label>
                                            <input type="text" name="kode_toko" class="form-control" value="{{ old('kode_toko', $item->kode_toko) }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Toko</label>
                                            <input type="text" name="nama_toko" class="form-control" value="{{ old('nama_toko', $item->nama_toko) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Lokasi Toko <span class="text-muted fw-normal">(Opsional)</span></label>
                                            <input type="text" name="lokasi_toko" class="form-control" value="{{ old('lokasi_toko', $item->lokasi_toko) }}" placeholder="Contoh: Jakarta Pusat / Mall Ambasador">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Link Toko (URL)</label>
                                            <input type="url" name="link_toko" class="form-control" value="{{ old('link_toko', $item->link_toko) }}" placeholder="https://tokopedia.com/namatoko">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-white fw-semibold">Update Toko</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Tidak ada data toko.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Toko -->
<div class="modal fade" id="modalTambahToko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Data Toko</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('toko.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Toko <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="text" name="kode_toko" class="form-control" placeholder="Contoh: TK-001 / TOKO-TKP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Toko</label>
                        <input type="text" name="nama_toko" class="form-control" placeholder="Contoh: Toko Official Gadget" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi Toko <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="text" name="lokasi_toko" class="form-control" placeholder="Contoh: Jakarta Pusat / Mall Ambasador">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Link Toko (URL)</label>
                        <input type="url" name="link_toko" class="form-control" placeholder="https://tokopedia.com/namatoko">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Toko</button>
                </div>
            </form>
        </div>
    </div>
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
    });
</script>
@endsection