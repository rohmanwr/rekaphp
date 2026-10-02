<x-guest-layout>
    <style>
        .login-page {
            --login-blue: #1a47a8;
            --login-blue-hover: #153b91;
            --login-border: #d7dfec;
        }

        .login-page .login-brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 1rem;
            border: 1px solid #d8e3f6;
            border-radius: 14px;
            background: #edf3ff;
            color: var(--login-blue);
        }

        .login-page h1 {
            color: #111827;
            font-size: 1.5rem;
            font-weight: 750;
            margin-bottom: .35rem;
        }

        .login-page .login-intro {
            color: #687386;
            font-size: .92rem;
            margin-bottom: 1.5rem;
        }

        .login-page .form-label {
            color: #263246;
            font-size: .88rem;
            font-weight: 700;
        }

        .login-page .input-group-text {
            min-width: 46px;
            justify-content: center;
            color: #69768a;
            background: #f7f9fc;
            border-color: var(--login-border);
        }

        .login-page .form-control {
            min-height: 48px;
            color: #151b27;
            background: #fff;
            border-color: var(--login-border);
            font-weight: 500;
        }

        .login-page .form-control:focus {
            z-index: 3;
            border-color: var(--login-blue);
            box-shadow: 0 0 0 .2rem rgba(26, 71, 168, .14);
        }

        .login-page .input-group:focus-within .input-group-text,
        .login-page .input-group:focus-within .login-password-toggle {
            border-color: var(--login-blue);
        }

        .login-page .login-password-toggle {
            min-width: 46px;
            color: #657187;
            background: #fff;
            border-color: var(--login-border);
        }

        .login-page .btn-primary {
            min-height: 48px;
            color: #fff !important;
            background: var(--login-blue) !important;
            border-color: var(--login-blue) !important;
            font-weight: 700;
            transition: background-color .18s ease, box-shadow .18s ease;
        }

        .login-page .btn-primary:hover,
        .login-page .btn-primary:focus {
            color: #fff !important;
            background: var(--login-blue-hover) !important;
            border-color: var(--login-blue-hover) !important;
            box-shadow: 0 5px 14px rgba(26, 71, 168, .22);
        }

        .login-page a {
            color: var(--login-blue) !important;
            font-weight: 700;
        }

        .login-page a:hover {
            color: var(--login-blue-hover) !important;
            text-decoration: underline !important;
        }

        [data-theme="dark"] .login-page h1,
        [data-theme="dark"] .login-page .form-label {
            color: #f3f5fa;
        }

        [data-theme="dark"] .login-page .login-intro {
            color: #b6c0d0;
        }

        [data-theme="dark"] .login-page .form-control,
        [data-theme="dark"] .login-page .login-password-toggle {
            color: #f3f5fa;
            background: #202736;
            border-color: #3d4b63;
        }

        [data-theme="dark"] .login-page .input-group-text {
            color: #b6c0d0;
            background: #252e40;
            border-color: #3d4b63;
        }
    </style>

    <div class="login-page">
        <div class="text-center">
            <div class="login-brand-mark">
                <i class="bi bi-phone-fill fs-3"></i>
            </div>
            <h1>Selamat datang</h1>
            <p class="login-intro">Masuk ke akun Rohman Store untuk melanjutkan.</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan username">
                </div>
                <x-input-error :messages="$errors->get('username')" class="text-danger small mt-1" />
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa Password?</a>
                    @endif
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" minlength="3" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                    <button type="button" id="togglePassword" class="btn login-password-toggle" aria-label="Tampilkan password" title="Tampilkan password" aria-pressed="false">
                        <i id="togglePasswordIcon" class="bi bi-eye"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="text-danger small mt-1" />
            </div>

            <div class="mb-4 form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label for="remember_me" class="form-check-label text-muted small user-select-none">Ingat Saya di perangkat ini</label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2 shadow-sm">
                    Masuk <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            <div class="text-center mt-4 pt-3 border-top">
                <span class="text-muted small">Belum punya akun?</span>
                <a href="{{ route('register') }}" class="small text-decoration-none ms-1">Daftar sekarang</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (!passwordInput || !toggleButton) return;

            toggleButton.addEventListener('click', function() {
                const isVisible = passwordInput.type === 'text';
                passwordInput.type = isVisible ? 'password' : 'text';
                toggleButton.setAttribute('aria-pressed', String(!isVisible));
                toggleButton.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
                toggleButton.setAttribute('title', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
                toggleIcon.className = isVisible ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        });
    </script>
</x-guest-layout>