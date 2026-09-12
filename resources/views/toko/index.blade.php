@extends('layouts.app')

@section('title', 'Master Data Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Master Data Toko</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahToko">
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
    <div class="card-body">
        <form id="searchForm" action="{{ route('toko.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan Kode Toko, Nama Toko, atau Lokasi..."
                    value="{{ $search }}"
                    autocomplete="off"
                    autofocus>
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
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode Toko</th>
                        <th>Nama Toko</th>
                        <th>Lokasi Toko</th>
                        <th>Link Toko</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tokos as $index => $item)
                    <tr>
                        <td class="text-center fw-semibold text-muted">
                            {{ $tokos->firstItem() ? $tokos->firstItem() + $index : $index + 1 }}
                        </td>
                        <td><span class="badge bg-dark fs-6">{{ $item->kode_toko }}</span></td>
                        <td class="fw-semibold">{{ $item->nama_toko }}</td>
                        <td><i class="bi bi-geo-alt text-danger"></i> {{ $item->lokasi_toko ?? '-' }}</td>
                        <td>
                            @if(!empty($item->link_toko))
                            <a href="{{ $item->link_toko }}" target="_blank" class="btn btn-xs btn-outline-primary p-1 px-2 text-decoration-none" style="font-size: 0.8rem;">
                                <i class="bi bi-box-arrow-up-right"></i> Kunjungi Link
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditToko{{ $item->id }}" title="Edit Data">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form action="{{ route('toko.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus toko ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger text-white fw-semibold" title="Hapus Data">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
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
                                            <label class="form-label fw-semibold">Kode Toko</label>
                                            <input type="text" name="kode_toko" class="form-control" value="{{ old('kode_toko', $item->kode_toko) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Toko</label>
                                            <input type="text" name="nama_toko" class="form-control" value="{{ old('nama_toko', $item->nama_toko) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Lokasi Toko</label>
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
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada data toko.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tokos->hasPages())
    <div class="card-footer bg-white">
        {{ $tokos->appends(['search' => $search])->links() }}
    </div>
    @endif
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
                        <label class="form-label fw-semibold">Kode Toko</label>
                        <input type="text" name="kode_toko" class="form-control" placeholder="Contoh: TK-001 / TOKO-TKP" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Toko</label>
                        <input type="text" name="nama_toko" class="form-control" placeholder="Contoh: Toko Official Gadget" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lokasi Toko</label>
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