<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <!-- Viewport Optimization khusus Android & HP Modern/Lawas (Mencegah Zoom otomatis & Overflow) -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Theme Color untuk Navbar Browser HP/Android -->
    <meta name="theme-color" content="#1e2229">

    <title>@yield('title', 'Rekap Bisnis HP')</title>

    <!-- Bootstrap 5 CSS & Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 270px;
            --primary-bg: #f4f6f9;
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
            background: #1e2229;
            background: -webkit-linear-gradient(180deg, #1e2229 0%, #111315 100%);
            background: linear-gradient(180deg, #1e2229 0%, #111315 100%);
            color: #fff;
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
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
        }

        #sidebar .sidebar-header {
            padding: 18px 16px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
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
            color: #8b95a5;
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
            color: #b0c4de;
            text-decoration: none;
            border-radius: 8px;
            -webkit-transition: all 0.2s ease;
            transition: all 0.2s ease;
            pointer-events: auto;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a:active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        #sidebar ul li a.active {
            color: #ffffff;
            background: #0d6efd;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35);
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
                    <i class="bi bi-phone-vibrate text-warning fs-4"></i>
                    <h5 class="mb-0 fw-bold text-white tracking-wide fs-6">Rohman Store</h5>
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

            <div class="p-3">
                @if($canAccess('pembelian'))
                @if(Route::has('pembelian.create'))
                <a href="{{ route('pembelian.create') }}" class="btn btn-warning w-100 fw-bold text-dark shadow-sm py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </a>
                @else
                <a href="{{ url('/pembelian/create') }}" class="btn btn-warning w-100 fw-bold text-dark shadow-sm py-2 d-flex align-items-center justify-content-center gap-2 rounded-3">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </a>
                @endif
                @endif
            </div>

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
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="toggleSidebarBtn" class="btn btn-light border p-2 me-1" title="Buka Menu" aria-label="Toggle Menu">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <span class="navbar-text fw-semibold text-dark small-mobile">
                            Halo, <strong class="text-primary">{{ Auth::user()->name ?? 'Admin' }}</strong>
                        </span>
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