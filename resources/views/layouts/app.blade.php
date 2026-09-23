<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rekap Bisnis HP')</title>

    <!-- Bootstrap 5 CSS & Icons via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-bg: #f4f6f9;
        }

        body {
            background-color: var(--primary-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styling Modern & Responsive */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e2229 0%, #111315 100%);
            color: #fff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
        }

        #sidebar .sidebar-header {
            padding: 22px 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        #sidebar ul.components {
            padding: 15px 12px;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        #sidebar ul li.sidebar-heading {
            padding: 15px 15px 8px;
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #8b95a5;
            letter-spacing: 0.8px;
        }

        #sidebar ul li {
            margin-bottom: 4px;
        }

        #sidebar ul li a {
            padding: 11px 16px;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            color: #b0c4de;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            position: relative;
            pointer-events: auto;
        }

        #sidebar ul li a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(3px);
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

        /* Content Area */
        #content {
            width: 100%;
            padding: 24px;
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
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
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* Responsive Breakpoints (< 992px / Mobile & Tablet) */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: calc(-1 * var(--sidebar-width));
                height: 100vh;
                box-shadow: none;
            }

            #sidebar.active {
                left: 0;
                box-shadow: 8px 0 25px rgba(0, 0, 0, 0.4);
            }

            #content {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <div id="wrapper">
        <!-- Sidebar Overlay (Menutup sidebar saat area luar disentuh di HP) -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-phone-vibrate text-warning fs-4"></i>
                    <h5 class="mb-0 fw-bold text-white tracking-wide">Rohman Store</h5>
                </div>
                <button type="button" id="closeSidebarBtn" class="btn btn-link text-white-50 d-lg-none p-0 border-0" aria-label="Tutup Menu">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>

            @php
            $authUser = Auth::user();
            $isAdmin = $authUser && strtolower($authUser->role) === 'admin';
            $permissions = $authUser->permissions;
            // Jika permissions null (belum pernah disimpan/user baru), dianggap aktif semua secara default
            $isNewOrEmpty = is_null($permissions);

            // Fungsi helper pengecekan hak akses per menu key
            $canAccess = function($key) use ($isAdmin, $permissions, $isNewOrEmpty) {
            if ($isAdmin) return true;
            if ($isNewOrEmpty) return true;
            return in_array($key, $permissions);
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
                <!-- Dashboard Selalu Muncul -->
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

                <!-- Histori Penjualan -->
                @if($canAccess('histori_penjualan'))
                <li>
                    <a href="{{ route('penjualan.histori') }}" class="{{ request()->routeIs('penjualan.histori') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i> Histori Penjualan
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

                <!-- Section Master Data -->
                @if($canAccess('master_data'))
                <li class="sidebar-heading mt-3">Master Data</li>
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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}" href="{{ route('user.index') }}">
                        <i class="bi bi-people-fill me-2"></i> Manajemen User
                    </a>
                </li>
                @endif
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded-3 shadow-sm mb-4 px-3 py-2">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="toggleSidebarBtn" class="btn btn-light border p-2 me-1" title="Buka Menu" aria-label="Toggle Menu">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <span class="navbar-text fw-semibold text-dark small-mobile">
                            Selamat Datang, <strong class="text-primary">{{ Auth::user()->name ?? 'Admin' }}</strong>
                        </span>
                    </div>

                    @auth
                    <div class="dropdown user-dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border py-1.5 px-3 rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Toggle Responsive Sidebar yang Dioptimalkan -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('show');
            }

            function closeSidebar() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('show');
            }

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (sidebar.classList.contains('active')) {
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

            // Tutup sidebar otomatis saat link di dalam sidebar diklik pada perangkat mobile
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
        });
    </script>
</body>

</html>