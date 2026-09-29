<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ScanController extends Controller
{
    /**
     * Menampilkan halaman Scan Serial Number
     */
    public function index()
    {
        // Cek jika file ada di resources/views/scan/sn.blade.php
        if (View::exists('scan.sn')) {
            return view('scan.sn');
        }

        // Cek jika file ada di resources/views/pembelian/scan_sn.blade.php
        if (View::exists('pembelian.scan_sn')) {
            return view('pembelian.scan_sn');
        }

        // Default: jika file ada di resources/views/scan-sn.blade.php
        return view('scan-sn');
    }
}
