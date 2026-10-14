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

    .toko-detail-toggle {
        min-width: 88px;
        border-radius: 999px;
    }

    .toko-detail-toggle i {
        display: inline-block;
        transition: transform .25s ease;
    }

    .toko-detail-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }

    .toko-detail-row>td {
        padding: 0 !important;
        border-top: 0;
    }

    .toko-detail-panel {
        margin: .25rem .75rem .75rem;
        padding: .9rem 1rem;
        border: 1px solid #dce2e6;
        border-radius: .65rem;
        background: #f8f9fa;
        box-shadow: 0 3px 10px rgba(33, 37, 41, .06);
    }

    .toko-detail-item {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .toko-detail-item a {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .toko-detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 1rem;
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

        .toko-table tbody tr.toko-main-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            grid-template-areas: "name detail actions";
            align-items: center;
            gap: .4rem;
            margin-bottom: 0;
            padding: .55rem;
            border: 1px solid #adb5bd;
            border-radius: .65rem;
            background: #fff;
        }

        .toko-table tbody td {
            display: block;
            min-width: 0;
            padding: .2rem !important;
            border: 0;
            text-align: left;
        }

        .toko-table tbody tr.toko-main-row td[data-label="No"] {
            display: none;
        }

        .toko-table tbody tr.toko-main-row td[data-label="Nama Toko"] {
            grid-area: name;
            overflow-wrap: anywhere;
        }

        .toko-table tbody tr.toko-main-row td[data-label="Detail"] {
            grid-area: detail;
            white-space: nowrap;
        }

        .toko-table tbody tr.toko-main-row td[data-label="Aksi"] {
            grid-area: actions;
        }

        .toko-table tbody td[colspan] {
            display: block;
            text-align: center;
        }

        .toko-table tbody tr.toko-detail-row {
            display: block;
            margin-bottom: .75rem;
        }

        .toko-table tbody tr.toko-detail-row>td {
            display: block;
            width: 100%;
        }

        .toko-detail-panel {
            margin: .25rem 0 0;
            padding: .8rem;
            border: 1px solid #dce2e6;
            border-radius: .65rem;
            background: #fff;
            box-shadow: none;
        }

        .toko-table .store-actions {
            flex-wrap: nowrap;
            gap: .35rem !important;
        }

        .toko-table .store-actions .btn {
            min-width: 38px;
            min-height: 38px;
        }

        .toko-detail-toggle {
            min-width: 0;
            min-height: 38px;
            padding: .35rem .7rem;
        }

        .toko-detail-grid {
            grid-template-columns: minmax(0, 1fr) !important;
            gap: .75rem !important;
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
                        <th class="text-center" style="width: 110px;">Detail</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokos as $index => $item)
                    <tr class="toko-main-row">
                        <td class="text-center fw-semibold text-muted" data-label="No">
                            {{ $loop->iteration }}
                        </td>
                        <td class="fw-semibold" data-label="Nama Toko">{{ $item->nama_toko }}</td>
                        <td class="text-center" data-label="Detail">
                            <button class="btn btn-sm btn-outline-secondary toko-detail-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#detailToko{{ $item->id }}" aria-expanded="false" aria-controls="detailToko{{ $item->id }}">
                                <i class="bi bi-chevron-down me-1"></i>Detail
                            </button>
                        </td>
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
                    <tr class="toko-detail-row">
                        <td colspan="4">
                            <div class="collapse" id="detailToko{{ $item->id }}">
                                <div class="toko-detail-panel">
                                    <div class="toko-detail-grid">
                                        <div class="toko-detail-item">
                                            <div class="small text-muted fw-semibold text-uppercase mb-1">Lokasi Toko</div>
                                            <div>{{ $item->lokasi_toko ?: 'Belum diisi' }}</div>
                                        </div>
                                        <div class="toko-detail-item">
                                            <div class="small text-muted fw-semibold text-uppercase mb-1">Link Toko</div>
                                            @if($item->link_toko)
                                            <a href="{{ $item->link_toko }}" target="_blank" rel="noopener noreferrer">{{ $item->link_toko }}</a>
                                            @else
                                            <div>Belum diisi</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
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
                        <td colspan="4" class="text-center py-4 text-muted">Tidak ada data toko.</td>
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