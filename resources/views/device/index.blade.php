@extends('layouts.app')

@section('title', 'Master Data Device')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Master Data Device</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahDevice">
        <i class="bi bi-plus-lg"></i> Tambah Device
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
        <form id="searchForm" action="{{ route('device.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    class="form-control border-start-0 ps-0"
                    placeholder="Cari berdasarkan Kode Device atau Nama Device..."
                    value="{{ $search }}"
                    autocomplete="off"
                    autofocus>
                @if(!empty($search))
                <a href="{{ route('device.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="bi bi-x-lg"></i> Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabel Master Device -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Kode Device</th>
                        <th>Nama Device</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $index => $item)
                    <tr>
                        <td class="text-center fw-semibold text-muted">
                            {{ $devices->firstItem() ? $devices->firstItem() + $index : $index + 1 }}
                        </td>
                        <td><span class="badge bg-secondary">{{ $item->kode_device }}</span></td>
                        <td class="fw-bold">{{ $item->nama_device }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-warning text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditDevice{{ $item->id }}" title="Edit Data">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('device.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data device ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger fw-semibold" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit Device -->
                    <div class="modal fade" id="modalEditDevice{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content text-start">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Master Device</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('device.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Kode Device <span class="text-danger">*</span></label>
                                            <input type="text" name="kode_device" class="form-control" value="{{ old('kode_device', $item->kode_device) }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_device" class="form-control" value="{{ old('nama_device', $item->nama_device) }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-warning text-white fw-semibold">Update Device</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Tidak ada data master device.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($devices->hasPages())
    <div class="card-footer bg-white">
        {{ $devices->appends(['search' => $search])->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Device -->
<div class="modal fade" id="modalTambahDevice" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Master Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('device.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Device <span class="text-danger">*</span></label>
                        <input type="text" name="kode_device" class="form-control" placeholder="Contoh: DEV-IP13PRO" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                        <input type="text" name="nama_device" class="form-control" placeholder="Contoh: iPhone 13 Pro 128GB Sierra Blue" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Device</button>
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