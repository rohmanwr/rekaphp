<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        try {
            var root = document.documentElement;
            root.setAttribute('data-theme', localStorage.getItem('rekap-theme') === 'dark' ? 'dark' : 'light');
            var accent = localStorage.getItem('rekap-accent');
            if (accent && /^#[0-9a-f]{6}$/i.test(accent)) root.style.setProperty('--accent', accent);
        } catch (error) {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>

    <title>{{ config('app.name', 'Rekap HP') }}</title>

    <!-- Bootstrap 5 CSS via CDN (Tanpa Vite) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --guest-bg: #f7f6fb;
            --guest-surface: #ffffff;
            --guest-text: #111111;
            --guest-muted: #5d5963;
            --accent: #ffc933;
        }

        [data-theme="dark"] {
            --guest-bg: #17151d;
            --guest-surface: #211e29;
            --guest-text: #f4f1f8;
            --guest-muted: #c2bacb;
        }

        body {
            background-color: var(--guest-bg);
            color: var(--guest-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
        }

        body.bg-light {
            background-color: var(--guest-bg) !important;
        }

        .card {
            color: var(--guest-text);
            background-color: var(--guest-surface);
        }

        .text-secondary,
        .text-muted {
            color: var(--guest-muted) !important;
            font-weight: 600;
        }

        .text-primary {
            color: var(--accent) !important;
        }

        .btn-primary,
        .bg-primary {
            color: #292536 !important;
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        [data-theme="dark"] .text-dark {
            color: var(--guest-text) !important;
        }

        [data-theme="dark"] .card {
            border-color: #3d3748;
        }

        .form-control,
        .form-select,
        .btn {
            font-weight: 600;
        }

        .login-screen {
            background-color: #f3f6fc;
            background-image: linear-gradient(rgba(26, 71, 168, .035) 1px, transparent 1px), linear-gradient(90deg, rgba(26, 71, 168, .035) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .login-screen .card {
            max-width: 460px !important;
            overflow: hidden;
            border: 1px solid #dce5f4 !important;
            border-radius: 12px;
            box-shadow: 0 18px 48px rgba(25, 48, 91, .12) !important;
        }

        .login-screen .mb-3.text-center a {
            color: #1a47a8 !important;
        }

        .login-screen .mb-3.text-center i {
            color: #1a47a8 !important;
            font-size: 1.8rem !important;
        }

        .login-screen .mb-3.text-center h3 {
            font-size: 1.15rem;
        }

        [data-theme="dark"] .login-screen {
            background-color: #171b25;
            background-image: linear-gradient(rgba(130, 159, 216, .045) 1px, transparent 1px), linear-gradient(90deg, rgba(130, 159, 216, .045) 1px, transparent 1px);
        }

        [data-theme="dark"] .login-screen .card {
            border-color: #34405a !important;
        }

        @media (max-width: 575.98px) {
            .login-screen {
                justify-content: flex-start !important;
                padding: 10vh 1rem 2rem !important;
            }

            .login-screen .card {
                max-width: 100% !important;
            }

            .login-screen .card-body {
                padding: 1.35rem !important;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-4 {{ request()->routeIs('login') ? 'login-screen' : '' }}">
        <div class="mb-3 text-center">
            <a href="/" class="text-decoration-none text-dark d-flex align-items-center gap-2">
                <i class="bi bi-phone-vibrate text-primary fs-1"></i>
                <h3 class="fw-bold mb-0">{{ request()->routeIs('login') ? 'Rohman Store' : 'Rekap HP' }}</h3>
            </a>
        </div>

        <div class="card border-0 shadow-sm w-100 {{ request()->routeIs('login') ? 'login-screen-card' : '' }}" style="max-width: 420px;">
            <div class="card-body p-4">
                {{ $slot }}
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>