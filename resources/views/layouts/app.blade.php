<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <!-- Viewport Optimization khusus Android & HP Modern/Lawas (Mencegah Zoom otomatis & Overflow) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="theme-color" content="#f7f6fb">

    <script>
        try {
            document.documentElement.setAttribute('data-theme', localStorage.getItem('rekap-theme') === 'dark' ? 'dark' : 'light');
        } catch (error) {
            document.documentElement.setAttribute('data-theme', 'light');
        }

        window.setRekapAccent = function(color, persist) {
            if (!/^#[0-9a-f]{6}$/i.test(color)) return;

            var red = parseInt(color.slice(1, 3), 16);
            var green = parseInt(color.slice(3, 5), 16);
            var blue = parseInt(color.slice(5, 7), 16);
            var channels = [red, green, blue].map(function(channel) {
                channel /= 255;
                return channel <= 0.04045 ? channel / 12.92 : Math.pow((channel + 0.055) / 1.055, 2.4);
            });
            var luminance = 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            var target = isDark ? [41, 37, 50] : [255, 255, 255];
            var mix = isDark ? 0.25 : 0.12;
            var softColor = [red, green, blue].map(function(channel, index) {
                return Math.round(channel * mix + target[index] * (1 - mix));
            });

            document.documentElement.style.setProperty('--accent', color);
            document.documentElement.style.setProperty('--accent-soft', 'rgb(' + softColor.join(', ') + ')');
            document.documentElement.style.setProperty('--accent-contrast', luminance > 0.179 ? '#292536' : '#ffffff');

            if (persist !== false) {
                try {
                    localStorage.setItem('rekap-accent', color);
                } catch (error) {}
            }
        };

        try {
            var savedAccent = localStorage.getItem('rekap-accent');
            if (savedAccent) window.setRekapAccent(savedAccent, false);
        } catch (error) {}
    </script>

    <title>@yield('title', 'Rekap Bisnis HP')</title>

    <!-- Bootstrap 5 CSS & Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 270px;
            --primary-bg: #f7f6fb;
            --surface: #ffffff;
            --surface-muted: #fbfaff;
            --body-text: #111111;
            --muted-text: #5d5963;
            --border-color: #e7e2ef;
            --sidebar-bg: #ffffff;
            --sidebar-text: #17141d;
            --sidebar-muted: #827a90;
            --accent: #1a47a8;
            --accent-soft: #fce9f5;
            --accent-contrast: #ffffff;
            color-scheme: light;
        }

        [data-theme="dark"] {
            --primary-bg: #17151d;
            --surface: #211e29;
            --surface-muted: #292532;
            --body-text: #f4f1f8;
            --muted-text: #c2bacb;
            --border-color: #3d3748;
            --sidebar-bg: #211e29;
            --sidebar-text: #e9e3f2;
            --sidebar-muted: #aaa1b8;
            --accent: #1a47a8;
            --accent-soft: #252e50;
            --accent-contrast: #ffffff;
            color-scheme: dark;
        }

        /* Mencegah Scroll Horisontal di Seluruh Halaman HP */
        html,
        body {
            max-width: 100vw;
            overflow-x: hidden !important;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            /* Mencegah warna kotak abu-abu saat di-tap di HP */
        }

        body {
            background-color: var(--primary-bg);
            color: var(--body-text);
            font-weight: 600;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            /* Support Safe Area untuk HP Android Poni / Notch */
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }

        #wrapper {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Sidebar Styling Modern & Compat dengan HP Lawas (Fallback Flex/Transform) */
        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            border-right: 1px solid var(--border-color);
            -webkit-transition: all 0.3s ease;
            transition: all 0.3s ease;
            z-index: 1050;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            box-shadow: 4px 0 18px rgba(44, 31, 70, 0.06);
        }

        #sidebar .sidebar-header {
            padding: 18px 16px;
            background: var(--surface-muted);
            border-bottom: 1px solid var(--border-color);
        }

        #sidebar ul.components {
            padding: 12px 10px;
            margin: 0;
            -webkit-box-flex: 1;
            -ms-flex: 1 1 auto;
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-y: contain;
            touch-action: pan-y;
        }

        #sidebar ul li.sidebar-heading {
            padding: 12px 12px 6px;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--sidebar-muted);
            letter-spacing: 0.8px;
        }

        #sidebar ul li {
            margin-bottom: 4px;
        }

        #sidebar ul li a {
            padding: 12px 14px;
            font-size: 0.9rem;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;
            pointer-events: auto;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a:active {
            color: var(--accent);
            background: var(--accent-soft);
        }

        #sidebar ul li a.active {
            color: var(--accent-contrast);
            background: var(--accent);
            font-weight: 600;
            box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 24%, transparent);
        }

        #sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Content Area Responsive & Pas Layar */
        #content {
            -webkit-box-flex: 1;
            -ms-flex: 1;
            flex: 1;
            width: 100%;
            max-width: 100%;
            padding: 18px 16px;
            min-height: 100vh;
            -webkit-transition: all 0.3s ease;
            transition: all 0.3s ease;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            overflow-x: hidden;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        .text-secondary {
            color: var(--muted-text) !important;
            font-weight: 600;
        }

        .form-control,
        .form-select,
        .btn {
            font-weight: 600;
        }

        /* Sidebar Backdrop / Overlay untuk Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1040;
            opacity: 0;
            -webkit-transition: opacity 0.3s ease;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Penyesuaian Kompatibilitas Tabel & Card di HP Android */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        [data-theme="dark"] #content {
            color: var(--body-text);
        }

        [data-theme="dark"] .navbar,
        [data-theme="dark"] .card,
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .dropdown-menu,
        [data-theme="dark"] .dashboard-panel,
        [data-theme="dark"] .dashboard-toolbar,
        [data-theme="dark"] .dashboard-kpi {
            color: var(--body-text) !important;
            background-color: var(--surface) !important;
            border-color: var(--border-color) !important;
        }

        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .bg-light,
        [data-theme="dark"] .table-light,
        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer {
            color: var(--body-text) !important;
            background-color: var(--surface-muted) !important;
            border-color: var(--border-color) !important;
        }

        [data-theme="dark"] .text-dark,
        [data-theme="dark"] .navbar-text {
            color: var(--body-text) !important;
        }

        [data-theme="dark"] .text-secondary {
            color: var(--muted-text) !important;
        }

        [data-theme="dark"] .text-muted {
            color: var(--muted-text) !important;
        }

        [data-theme="dark"] .border,
        [data-theme="dark"] .border-bottom,
        [data-theme="dark"] .border-top {
            border-color: var(--border-color) !important;
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select,
        [data-theme="dark"] .input-group-text {
            color: var(--body-text);
            background-color: #191720;
            border-color: var(--border-color);
        }

        [data-theme="dark"] .form-control::placeholder {
            color: var(--muted-text);
        }

        [data-theme="dark"] .table {
            --bs-table-color: var(--body-text);
            --bs-table-bg: var(--surface);
            --bs-table-border-color: var(--border-color);
            --bs-table-hover-color: var(--body-text);
            --bs-table-hover-bg: var(--surface-muted);
        }

        [data-theme="dark"] .dropdown-item {
            color: var(--body-text);
        }

        [data-theme="dark"] .dropdown-item:hover {
            background-color: var(--accent-soft);
        }

        [data-theme="dark"] .btn-light {
            color: var(--body-text);
            background-color: var(--surface-muted);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .sidebar-overlay {
            background: rgba(15, 12, 20, 0.58);
        }

        .btn-primary {
            color: var(--accent-contrast);
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            color: var(--accent-contrast);
            background-color: color-mix(in srgb, var(--accent) 86%, #000000);
            border-color: color-mix(in srgb, var(--accent) 86%, #000000);
        }

        .btn-outline-primary {
            color: var(--accent);
            border-color: var(--accent);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: var(--accent-contrast);
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .text-primary {
            color: var(--accent) !important;
        }

        .bg-primary {
            background-color: var(--accent) !important;
        }

        .border-primary {
            border-color: var(--accent) !important;
        }

        .theme-color-menu {
            width: 248px;
            padding: 1rem;
        }

        .theme-color-swatches {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: .6rem;
        }

        .theme-color-swatch {
            width: 34px;
            height: 34px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px var(--border-color);
        }

        .theme-color-swatch[aria-pressed="true"] {
            outline: 2px solid var(--body-text);
            outline-offset: 2px;
        }

        .theme-custom-color {
            width: 48px;
            height: 38px;
            padding: .2rem;
            cursor: pointer;
        }

        /* Optimasi Tampilan Android & Tablet (< 992px) */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -270px;
                height: 100vh;
                height: 100dvh;
                max-height: 100dvh;
                overflow: hidden;
                padding-top: env(safe-area-inset-top);
                padding-bottom: env(safe-area-inset-bottom);
                box-shadow: none;
                -webkit-transform: translateX(0);
                transform: translateX(0);
            }

            #sidebar .sidebar-header,
            #sidebar>.p-3 {
                -webkit-box-flex: 0;
                -ms-flex: 0 0 auto;
                flex: 0 0 auto;
            }

            #sidebar.active {
                left: 0;
                box-shadow: 8px 0 25px rgba(0, 0, 0, 0.4);
            }

            #content {
                padding: 12px 10px;
                /* Padding lebih ramping untuk layar HP agar muat lebih luas */
            }

            .small-mobile {
                font-size: 0.82rem;
            }

            /* Tombol Akses Mobile touch-friendly */
            .btn-sm,
            .btn {
                min-height: 38px;
                display: -webkit-inline-box;
                display: -ms-inline-flexbox;
                display: inline-flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <div id="wrapper">
        <!-- Sidebar Overlay -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <!-- Sidebar Navigation -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-phone-vibrate text-primary fs-4"></i>
                    <a href="{{ route('pembelian.index') }}" class="mb-0 fw-bold text-decoration-none tracking-wide fs-6" style="color: var(--sidebar-text);">Rohman Store</a>
                </div>
                <button type="button" id="closeSidebarBtn" class="btn btn-link text-white-50 d-lg-none p-1 border-0 text-decoration-none" aria-label="Tutup Menu">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>

            @php
            $authUser = Auth::user();
            $isAdmin = $authUser && strtolower($authUser->role ?? '') === 'admin';
            $permissions = $authUser ? $authUser->permissions : null;
            $isNewOrEmpty = is_null($permissions);

            $canAccess = function($key) use ($isAdmin, $permissions, $isNewOrEmpty) {
            if ($isAdmin) return true;
            if ($isNewOrEmpty) return true;
            return is_array($permissions) && in_array($key, $permissions);
            };
            @endphp

            <ul class="list-unstyled components">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <!-- Rekap Pembelian -->
                @if($canAccess('pembelian'))
                <li>
                    <a href="{{ route('pembelian.index') }}" class="{{ request()->routeIs('pembelian.*') && !request()->routeIs('pembelian.create') && !request()->routeIs('pembelian.siap_jual') && !request()->routeIs('pembelian.histori_rekap') ? 'active' : '' }}">
                        <i class="bi bi-cart-check"></i> Rekap Pembelian
                    </a>
                </li>
                @endif

                <!-- Siap Jual -->
                @if($canAccess('siap_jual'))
                <li>
                    <a href="{{ route('pembelian.siap_jual') }}" class="{{ request()->routeIs('pembelian.siap_jual') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill text-warning"></i> Siap Jual
                    </a>
                </li>
                @endif

                <!-- Menu Fitur Tools / Scanner -->
                @if($canAccess('pembelian') || $canAccess('scan_sn'))
                <li class="sidebar-heading mt-2">Fitur Scanner</li>
                <li>
                    @if(Route::has('scan.sn'))
                    <a href="{{ route('scan.sn') }}" class="{{ request()->routeIs('scan.sn') ? 'active' : '' }}">
                        <i class="bi bi-qr-code-scan text-primary"></i> Scan Serial Number
                    </a>
                    @else
                    <a href="{{ url('/scan-sn') }}" class="{{ request()->is('scan-sn') ? 'active' : '' }}">
                        <i class="bi bi-qr-code-scan text-primary"></i> Scan Serial Number
                    </a>
                    @endif
                </li>
                @endif

                <!-- Histori Penjualan -->
                @if($canAccess('histori_penjualan'))
                <li class="sidebar-heading mt-2">Histori Data</li>
                <li>
                    <a href="{{ route('penjualan.histori') }}" class="{{ request()->routeIs('penjualan.histori') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i> Histori Penjualan
                    </a>
                </li>
                <li>
                    <a href="{{ route('penjualan.retur.index') }}" class="{{ request()->routeIs('penjualan.retur.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-return-left text-warning"></i> Retur Barang
                    </a>
                </li>
                @endif

                <!-- Histori Rekap -->
                @if($canAccess('histori_rekap'))
                <li>
                    <a href="{{ route('pembelian.histori_rekap') }}" class="{{ request()->routeIs('pembelian.histori_rekap') ? 'active' : '' }}">
                        <i class="bi bi-clock-history text-info"></i> Histori Rekap
                    </a>
                </li>
                @endif

                <!-- Keuangan -->
                @if($canAccess('keuangan'))
                <li class="sidebar-heading mt-2">Keuangan</li>
                <li>
                    <a href="{{ route('keuangan.index') }}" class="{{ request()->routeIs('keuangan.*') ? 'active' : '' }}">
                        <i class="bi bi-wallet2 text-success"></i> Keuangan & Aset
                    </a>
                </li>
                @endif

                <!-- Master Data -->
                @if($canAccess('master_data'))
                <li class="sidebar-heading mt-2">Master Data</li>
                <li>
                    <a href="{{ route('barang.index') }}" class="{{ request()->routeIs('barang.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Nama Barang
                    </a>
                </li>
                @if(Route::has('toko.index'))
                <li>
                    <a href="{{ route('toko.index') }}" class="{{ request()->routeIs('toko.*') ? 'active' : '' }}">
                        <i class="bi bi-shop"></i> Nama Toko
                    </a>
                </li>
                @endif
                @endif

                <!-- Manajemen User -->
                @if($canAccess('user_management'))
                <li>
                    <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Manajemen User
                    </a>
                </li>
                @endif
            </ul>
        </nav>

        <!-- Page Content Area -->
        <div id="content">
            <!-- Top Navbar Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-3 px-2 py-2">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="toggleSidebarBtn" class="btn btn-light border p-2 me-1" title="Buka Menu" aria-label="Toggle Menu">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <a href="{{ route('pembelian.index') }}" class="navbar-brand d-lg-none fw-bold text-decoration-none mb-0" style="color: var(--accent);">Rohman Store</a>
                        <span class="navbar-text fw-semibold text-dark small-mobile d-none d-lg-inline">
                            Halo, <strong class="text-primary">{{ Auth::user()->name ?? 'Admin' }}</strong>
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="themeToggle" class="btn btn-light border p-2" aria-label="Aktifkan mode gelap" title="Aktifkan mode gelap">
                            <i id="themeToggleIcon" class="bi bi-moon-stars-fill"></i>
                        </button>
                        <div class="dropdown">
                            <button type="button" id="themeColorMenuToggle" class="btn btn-light border p-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Ubah warna tema" title="Ubah warna tema">
                                <i class="bi bi-palette2"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end theme-color-menu" aria-labelledby="themeColorMenuToggle">
                                <div class="fw-bold mb-1">Warna tema</div>
                                <div class="small text-muted mb-3">Pilih warna aksen aplikasi.</div>
                                <div class="theme-color-swatches mb-3" role="group" aria-label="Pilihan warna tema">
                                    <button type="button" class="theme-color-swatch" data-theme-color="#1a47a8" style="background-color:#1a47a8" aria-label="Warna default biru" title="Default · RGB 26, 71, 168" aria-pressed="false"></button>
                                    <button type="button" class="theme-color-swatch" data-theme-color="#16877d" style="background-color:#16877d" aria-label="Teal" title="Teal" aria-pressed="false"></button>
                                    <button type="button" class="theme-color-swatch" data-theme-color="#bd4969" style="background-color:#bd4969" aria-label="Merah muda" title="Merah muda" aria-pressed="false"></button>
                                    <button type="button" class="theme-color-swatch" data-theme-color="#315cb8" style="background-color:#315cb8" aria-label="Biru" title="Biru" aria-pressed="false"></button>
                                    <button type="button" class="theme-color-swatch" data-theme-color="#c36a24" style="background-color:#c36a24" aria-label="Oranye" title="Oranye" aria-pressed="false"></button>
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <label for="customThemeColor" class="small fw-semibold mb-0">Warna kustom</label>
                                    <input id="customThemeColor" class="form-control form-control-color theme-custom-color" type="color" value="#1a47a8" aria-label="Pilih warna kustom">
                                </div>
                                <button type="button" id="resetThemeColor" class="btn btn-sm btn-outline-secondary w-100 mt-3">Kembali ke warna default</button>
                            </div>
                        </div>
                        @auth
                        <div class="dropdown user-dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-1 border py-1.5 px-2.5 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5 text-secondary"></i>
                                <span class="fw-semibold small d-none d-sm-inline">{{ Auth::user()->name }}</span>
                                <i class="bi bi-chevron-down small text-muted"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                                @if(Route::has('profile.edit'))
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-gear me-2 text-muted"></i> Pengaturan Profil
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                @endif
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger fw-semibold">
                                            <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endauth
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="container-fluid p-0">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Toggle Responsive Sidebar (Optimal Touch Support Android) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
            var closeSidebarBtn = document.getElementById('closeSidebarBtn');
            var sidebarOverlay = document.getElementById('sidebarOverlay');
            var themeToggle = document.getElementById('themeToggle');
            var themeToggleIcon = document.getElementById('themeToggleIcon');
            var themeColor = document.querySelector('meta[name="theme-color"]');
            var customThemeColor = document.getElementById('customThemeColor');
            var colorSwatches = document.querySelectorAll('[data-theme-color]');
            var resetThemeColor = document.getElementById('resetThemeColor');

            function updateAccentSelection(color) {
                colorSwatches.forEach(function(swatch) {
                    swatch.setAttribute('aria-pressed', swatch.dataset.themeColor.toLowerCase() === color.toLowerCase() ? 'true' : 'false');
                });
                if (customThemeColor) customThemeColor.value = color;
            }

            var initialAccent = document.documentElement.style.getPropertyValue('--accent').trim() || getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();
            updateAccentSelection(initialAccent);

            colorSwatches.forEach(function(swatch) {
                swatch.addEventListener('click', function() {
                    window.setRekapAccent(this.dataset.themeColor);
                    updateAccentSelection(this.dataset.themeColor);
                });
            });

            if (customThemeColor) {
                customThemeColor.addEventListener('input', function() {
                    window.setRekapAccent(this.value);
                    updateAccentSelection(this.value);
                });
            }

            if (resetThemeColor) {
                resetThemeColor.addEventListener('click', function() {
                    window.setRekapAccent('#1a47a8');
                    updateAccentSelection('#1a47a8');
                });
            }

            function updateThemeToggle(theme) {
                var isDark = theme === 'dark';
                themeToggleIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
                themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
                themeToggle.setAttribute('title', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
                if (themeColor) themeColor.setAttribute('content', isDark ? '#17151d' : '#f7f6fb');
            }

            var currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeToggle(currentTheme);

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', currentTheme);
                    try {
                        localStorage.setItem('rekap-theme', currentTheme);
                    } catch (error) {}

                    var savedAccent = '';
                    try {
                        savedAccent = localStorage.getItem('rekap-accent') || '';
                    } catch (error) {}

                    if (savedAccent) {
                        window.setRekapAccent(savedAccent, false);
                    } else {
                        document.documentElement.style.removeProperty('--accent');
                        document.documentElement.style.removeProperty('--accent-soft');
                        document.documentElement.style.removeProperty('--accent-contrast');
                    }

                    updateThemeToggle(currentTheme);
                });
            }

            function openSidebar() {
                if (sidebar) sidebar.classList.add('active');
                if (sidebarOverlay) sidebarOverlay.classList.add('show');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('active');
                if (sidebarOverlay) sidebarOverlay.classList.remove('show');
            }

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar && sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeSidebar();
                });
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    closeSidebar();
                });
            }

            var sidebarLinks = document.querySelectorAll('#sidebar a');
            for (var i = 0; i < sidebarLinks.length; i++) {
                sidebarLinks[i].addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            }
        });
    </script>
</body>

</html>