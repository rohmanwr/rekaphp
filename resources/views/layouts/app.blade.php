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
        }

        /* Sidebar Styling */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }

        #sidebar {
            min-width: 250px;
            max-width: 250px;
            background: #212529;
            color: #fff;
            transition: all 0.3s;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background: #1a1d20;
        }

        #sidebar ul.components {
            padding: 15px 0;
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
            padding: 25px;
            min-height: 100vh;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }
    </style>
</head>

<body>

    <div id="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center gap-2">
                <i class="bi bi-phone-vibrate text-warning fs-4"></i>
                <h5 class="mb-0 fw-bold text-white">Rekap HP</h5>
            </div>

            <div class="p-3">
                @if(Route::has('pembelian.create'))
                <a href="{{ route('pembelian.create') }}" class="btn btn-warning w-100 fw-bold text-dark d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </a>
                @else
                <button class="btn btn-warning w-100 fw-bold text-dark d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalTambahPembelian">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Pembelian
                </button>
                @endif
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('penjualan.siap-jual') }}" class="{{ request()->routeIs('penjualan.siap-jual') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i> Barang Siap Jual
                    </a>
                </li>
                <li>
                    <a href="{{ route('penjualan.detail-barang') }}" class="{{ request()->routeIs('penjualan.detail-barang') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i> Detail Barang
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar Header -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white rounded shadow-sm mb-4 px-3">
                <div class="container-fluid p-0">
                    <span class="navbar-text fw-semibold text-dark">
                        Selamat Datang, <strong class="text-primary">{{ Auth::user()->name ?? 'Admin' }}</strong>
                    </span>

                    @auth
                    <div class="dropdown user-dropdown">
                        <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-secondary"></i>
                            <span class="fw-semibold small">{{ Auth::user()->name }}</span>
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
</body>

</html>