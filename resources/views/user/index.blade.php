@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Daftar Pengguna Sistem</h3>
        <p class="text-muted small mb-0">Kelola daftar akun, hak akses, dan status pengguna aplikasi.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Hak Akses / Status</th>
                        <th>Tanggal Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $user->email }}</span>
                        </td>
                        <td>
                            @php
                            // Menyesuaikan kolom role/status di database Anda (default mengecek $user->role)
                            $role = strtolower($user->role ?? 'user');
                            @endphp

                            @if($role === 'admin')
                            <span class="badge bg-danger px-3 py-2 text-uppercase" style="font-size: 0.70rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i> Admin
                            </span>
                            @else
                            <span class="badge bg-secondary px-3 py-2 text-uppercase" style="font-size: 0.70rem;">
                                <i class="bi bi-person-fill me-1"></i> User
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted small">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pengguna yang terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection