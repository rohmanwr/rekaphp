<x-guest-layout>
    <!-- Brand / Header Section -->
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
            <i class="bi bi-phone-fill fs-3"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">Selamat Datang Kembali</h4>
        <p class="text-muted small">Silakan masuk ke akun Rekap HP Anda</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold text-secondary small">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                <input id="email" type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
            </div>
            <x-input-error :messages="$errors->get('email')" class="text-danger small mt-1" />
        </div>

        <!-- Password -->
        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label fw-semibold text-secondary small mb-0">Password</label>
                @if (Route::has('password.request'))
                <!-- Pastikan route-nya adalah password.request tanpa tambahan angka -->
                <a href="{{ route('password.request') }}" class="small text-decoration-none text-primary fw-medium" style="font-size: 0.85rem;">
                    Lupa Password?
                </a>
                @endif
            </div>
            <div class="input-group mt-1">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                <input id="password" type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <x-input-error :messages="$errors->get('password')" class="text-danger small mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label text-muted small user-select-none">
                Ingat Saya di perangkat ini
            </label>
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Log In
            </button>
        </div>

        <!-- Register Link Divider -->
        <div class="text-center mt-4">
            <span class="text-muted small">Belum punya akun?</span>
            <a href="{{ route('register') }}" class="small text-decoration-none fw-bold text-primary ms-1">
                Daftar Sekarang
            </a>
        </div>
    </form>
</x-guest-layout>