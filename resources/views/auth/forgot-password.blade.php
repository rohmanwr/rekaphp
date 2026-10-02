<x-guest-layout>
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-3" style="width: 60px; height: 60px;">
            <i class="bi bi-key-fill fs-3"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Reset Password Langsung</h4>
        <p class="text-muted small">Masukkan username akun Anda dan tentukan password baru.</p>
    </div>

    <!-- Alert Notifikasi Sukses -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Session Status bawaan (jika ada) -->
    <x-auth-session-status class="mb-4 text-success small" :status="session('status')" />

    <form method="POST" action="{{ url('/forgot-password') }}">
        @csrf

        <!-- Username -->
        <div class="mb-3">
            <label for="username" class="form-label fw-semibold text-secondary small">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input id="username" type="text" class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan username">
            </div>
            <x-input-error :messages="$errors->get('username')" class="text-danger small mt-1" />
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold text-secondary small">Password Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" name="password" required minlength="3" placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock-fill"></i></span>
                <input id="password_confirmation" type="password" class="form-control border-start-0 ps-0" name="password_confirmation" required minlength="3" placeholder="••••••••">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                <i class="bi bi-check-circle me-1"></i> Ubah Password Sekarang
            </button>
        </div>

        <!-- Back to Login -->
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small text-decoration-none text-muted">
                <i class="bi bi-arrow-left"></i> Kembali ke Halaman Login
            </a>
        </div>
    </form>
</x-guest-layout>