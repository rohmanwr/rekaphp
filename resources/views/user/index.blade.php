@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<style>
    .user-management-table td,
    .user-management-table th {
        vertical-align: middle;
    }

    .user-management-table .user-username-cell {
        overflow-wrap: anywhere;
    }

    .user-management-table tbody tr[hidden] {
        display: none !important;
    }

    @media (max-width: 767.98px) {
        .user-management-table {
            display: block;
            width: 100%;
        }

        .user-management-table thead {
            display: none;
        }

        .user-management-table tbody {
            display: grid;
            gap: .75rem;
            padding: .75rem;
        }

        .user-management-table tbody tr {
            display: block;
            border: 1px solid #adb5bd;
            border-radius: .5rem;
            background: #fff;
            overflow: hidden;
        }

        .user-management-table tbody td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .7rem .75rem;
            border: 0;
            border-bottom: 1px solid #dee2e6;
            white-space: normal;
            text-align: right;
        }

        .user-management-table tbody td::before {
            content: attr(data-label);
            flex: 0 0 34%;
            color: #495057;
            font-size: .8rem;
            font-weight: 700;
            text-align: left;
        }

        .user-management-table tbody td:last-child {
            border-bottom: 0;
        }

        .user-management-table tbody td[data-label="Aksi"] {
            display: block;
            text-align: left;
        }

        .user-management-table tbody td[data-label="Aksi"]::before {
            display: block;
            margin-bottom: .5rem;
        }

        .user-management-table form select {
            width: min(100%, 170px);
        }

        .user-management-table .user-actions {
            justify-content: flex-start !important;
            flex-wrap: wrap;
        }

        .user-management-table .user-actions .btn {
            min-width: 42px;
            min-height: 42px;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Daftar Pengguna Sistem</h3>
        <p class="text-muted small mb-0">Kelola daftar akun, hak akses peran, status keaktifan, dan izin halaman pengguna.</p>
    </div>
    <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
        <i class="bi bi-person-plus-fill me-2"></i>Tambah User
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
    <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Terjadi kesalahan input:</div>
    <ul class="mb-0 small">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-white border-bottom p-3">
        <label for="userSearch" class="form-label fw-semibold text-secondary small mb-2">Cari berdasarkan username atau email</label>
        <div class="input-group">
            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
            <input id="userSearch" type="search" class="form-control" placeholder="Ketik username atau email..." autocomplete="off" aria-label="Cari pengguna berdasarkan username atau email">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive mb-0">
            <table class="table table-hover table-bordered align-middle mb-0 user-management-table">
                <thead class="table-light text-uppercase fs-7 text-secondary tracking-wider">
                    <tr>
                        <th class="text-center py-3" style="width: 60px;">No</th>
                        <th class="py-3">Nama Pengguna</th>
                        <th class="py-3">Username</th>
                        <th class="py-3">Email</th>
                        <th class="py-3" style="width: 160px;">Hak Akses (Role)</th>
                        <th class="text-center py-3" style="width: 110px;">Status</th>
                        <th class="py-3">Tanggal Terdaftar</th>
                        <th class="text-center py-3" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                    @forelse($users as $index => $user)
                    <tr data-search="{{ strtolower(($user->username ?? '') . ' ' . $user->email) }}">
                        <td class="text-center text-muted fw-semibold" data-label="No">{{ $index + 1 }}</td>
                        <td data-label="Pengguna">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.9rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="fw-bold text-dark">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td class="user-username-cell" data-label="Username">{{ $user->username ?? '-' }}</td>
                        <td data-label="Email">
                            <span class="text-secondary">{{ $user->email }}</span>
                        </td>
                        <td data-label="Role">
                            <!-- Form Dropdown untuk Update Role secara Langsung -->
                            <form action="{{ route('user.update-role', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="role" class="form-select form-select-sm fw-semibold shadow-none {{ strtolower($user->role) === 'admin' ? 'text-danger border-danger bg-danger-subtle' : 'text-secondary border-secondary bg-light' }}" onchange="this.form.submit()">
                                    <option value="user" {{ strtolower($user->role ?? 'user') === 'user' ? 'selected' : '' }}>User</option>
                                    <option value="admin" {{ strtolower($user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </form>
                        </td>
                        <td class="text-center" data-label="Status">
                            @php $statusVal = strtolower($user->status ?? 'active'); @endphp
                            @if($statusVal === 'active')
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">Aktif</span>
                            @else
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold">Nonaktif</span>
                            @endif
                        </td>
                        <td data-label="Terdaftar">
                            <span class="text-muted small">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                            </span>
                        </td>
                        <td class="text-center" data-label="Aksi">
                            <div class="user-actions d-flex justify-content-center gap-2">
                                <!-- Tombol Edit (Memicu Modal) -->
                                <button type="button" class="btn btn-sm btn-light border text-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit User & Hak Akses">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <!-- Tombol Reset Password -->
                                <form action="{{ route('user.reset-password', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mereset password user {{ $user->name }} menjadi 12345?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-light border text-info shadow-sm" title="Reset Password ke 12345">
                                        <i class="bi bi-key-fill"></i>
                                    </button>
                                </form>

                                <!-- Tombol Nonaktif / Aktifkan -->
                                <form action="{{ route('user.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-light border {{ $statusVal === 'active' ? 'text-secondary' : 'text-success' }} shadow-sm" title="{{ $statusVal === 'active' ? 'Nonaktifkan User' : 'Aktifkan User' }}">
                                        <i class="bi {{ $statusVal === 'active' ? 'bi-slash-circle' : 'bi-check-circle-fill' }}"></i>
                                    </button>
                                </form>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger shadow-sm" title="Hapus User">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Edit User & Toggle Hak Akses Halaman -->
                            <div class="modal fade text-start" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <form action="{{ route('user.update', $user->id) }}" method="POST" class="w-100">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header bg-light px-4 py-3">
                                                <h5 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-person-gear text-primary me-2"></i> Edit Pengguna: {{ $user->name }}
                                                </h5>
                                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body p-4 text-start">
                                                <!-- Informasi Dasar -->
                                                <div class="row g-3 mb-4">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-secondary small">Nama Pengguna</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold text-secondary small">Email</label>
                                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-semibold text-secondary small">Password Baru <span class="text-muted fw-normal">(Opsional)</span></label>
                                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                                                    </div>
                                                </div>

                                                <hr class="text-muted opacity-25 my-3">

                                                <!-- Pengaturan Toggle Hak Akses Halaman -->
                                                <div>
                                                    <label class="form-label fw-bold text-dark mb-1">
                                                        <i class="bi bi-shield-lock-fill text-warning me-1"></i> Hak Akses Halaman Sistem
                                                    </label>
                                                    <p class="text-muted small mb-3">Secara *default* aktif (ON). Nonaktifkan toggle di bawah jika ingin membatasi akses halaman user ini:</p>

                                                    @php
                                                    $daftarHalaman = [
                                                    'pembelian' => 'Rekap Pembelian',
                                                    'histori_rekap' => 'Histori Rekap',
                                                    'siap_jual' => 'Siap Jual',
                                                    'histori_penjualan' => 'Histori Penjualan',
                                                    'master_data' => 'Master Data (Nama Barang & Toko)',
                                                    'user_management' => 'Manajemen User'
                                                    ];

                                                    $userPermissions = $user->permissions;
                                                    $isNewOrEmpty = is_null($userPermissions);
                                                    @endphp

                                                    <div class="row g-2 border p-3 rounded-3 bg-light">
                                                        @foreach($daftarHalaman as $key => $label)
                                                        @php
                                                        // Jika kosong/null (user lama/baru), centang (checked). Jika sudah diatur, cek di dalam array.
                                                        $isChecked = $isNewOrEmpty ? true : in_array($key, $userPermissions);
                                                        @endphp
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch py-1">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}" id="perm_{{ $user->id }}_{{ $key }}" {{ $isChecked ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-medium text-dark small cursor-pointer" for="perm_{{ $user->id }}_{{ $key }}">
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light px-4 py-3">
                                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                            Belum ada data pengguna yang terdaftar di sistem.
                        </td>
                    </tr>
                    @endforelse
                    <tr id="userSearchEmpty" class="d-none">
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada pengguna dengan username atau email tersebut.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true" data-open="{{ $errors->hasAny(['name', 'username', 'email', 'password', 'role']) ? 'true' : 'false' }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('user.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="createUserModalLabel"><i class="bi bi-person-plus text-primary me-2"></i>Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newUserName" class="form-label fw-semibold">Nama Pengguna</label>
                        <input id="newUserName" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autocomplete="name">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="newUserUsername" class="form-label fw-semibold">Username</label>
                        <input id="newUserUsername" type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required autocomplete="username">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="newUserEmail" class="form-label fw-semibold">Email</label>
                        <input id="newUserEmail" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="newUserPassword" class="form-label fw-semibold">Password</label>
                        <input id="newUserPassword" type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="3" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="newUserPasswordConfirmation" class="form-label fw-semibold">Konfirmasi Password</label>
                        <input id="newUserPasswordConfirmation" type="password" name="password_confirmation" class="form-control" minlength="3" required autocomplete="new-password">
                    </div>
                    <div>
                        <label for="newUserRole" class="form-label fw-semibold">Role</label>
                        <select id="newUserRole" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearch');
        const userRows = Array.from(document.querySelectorAll('#userTableBody tr[data-search]'));
        const emptyState = document.getElementById('userSearchEmpty');

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            userRows.forEach(function(row) {
                const matches = row.dataset.search.includes(query);
                row.hidden = !matches;
                if (matches) visibleCount++;
            });

            emptyState.classList.toggle('d-none', userRows.length === 0 || visibleCount > 0);
        });

        const createUserModal = document.getElementById('createUserModal');
        if (createUserModal && createUserModal.dataset.open === 'true') {
            bootstrap.Modal.getOrCreateInstance(createUserModal).show();
        }
    });
</script>
@endsection