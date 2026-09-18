<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rekap Bisnis HP')</title>

    <!-- Bootstrap 5 CSS & Icons via CDN (Tanpa Vite/NPM) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Base Styling */
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #212529;
            color: #fff;
            transition: all 0.3s ease-in-out;
            z-index: 1045;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: #1a1d20;
        }

        #sidebar ul.components {
            padding: 15px 0;
        }

        #sidebar ul li.sidebar-heading {
            padding: 10px 20px 5px;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #6c757d;
            letter-spacing: 0.5px;
        }

        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: #ced4da;
            text-decoration: none;
            transition: all 0.2s;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a.active {
            color: #fff;
            background: #0d6efd;
        }

        #sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        /* Sidebar Overlay / Backdrop untuk Tampilan Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            transition: all 0.3s ease-in-out;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Responsive Breakpoints untuk Android / Layar Kecil (< 992px) */
        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                height: 100vh;
                overflow-y: auto;
            }

            #sidebar.active {
                left: 0;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            }

            #content {
                padding: 15px;
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
                    <h5 class="mb-0 fw-bold text-white">Rekap HP</h5>
                </div>
                <button type="button" id="closeSidebarBtn" class="btn btn-link text-white-50 d-lg-none p-0 border-0">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>

            <div class="p-3">
                @if(Route::has('pembelian.create'))
                <a href="{{ route('pembelian.create') }}" class="btn btn-warning w-100 fw-bold text-dark d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </a>
                @else
                <a href="{{ url('/pembelian/create') }}" class="btn btn-warning w-100 fw-bold text-dark d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </a>
                @endif
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('pembelian.index') }}" class="{{ request()->routeIs('pembelian.*') && !request()->routeIs('pembelian.create') && !request()->routeIs('pembelian.siap_jual') ? 'active' : '' }}">
                        <i class="bi bi-cart-check"></i> Rekap Pembelian
                    </a>
                </li>
                <!-- Menu Siap Jual -->
                <li>
                    <a href="{{ route('pembelian.siap_jual') }}" class="{{ request()->routeIs('pembelian.siap_jual') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill text-warning"></i> Siap Jual
                    </a>
                </li>
                <!-- Menu Histori Penjualan Tunggal (Tanpa Dropdown) -->
                <li>
                    <a href="{{ route('penjualan.histori') }}" class="{{ request()->routeIs('penjualan.histori') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Histori Penjualan
                    </a>
                </li>

                <!-- Section Master Data -->
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
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4 px-3 py-2">
                <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" id="toggleSidebarBtn" class="btn btn-light border p-2 me-1" title="Buka Menu">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <span class="navbar-text fw-semibold text-dark small-mobile">
                            Selamat Datang, <strong class="text-primary">{{ Auth::user()->name ?? 'Admin' }}</strong>
                        </span>
                    </div>

                    @auth
                    <div class="dropdown user-dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-secondary"></i>
                            <span class="fw-semibold small d-none d-sm-inline">{{ Auth::user()->name }}</span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
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

    <!-- Script Toggle Responsive Sidebar -->
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
                toggleSidebarBtn.addEventListener('click', function() {
                    if (sidebar.classList.contains('active')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });
            }

            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', closeSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
        });
    </script>
</body>

</html>