@extends('layouts.app')

@section('title', 'Tambah Rekap Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Tambah Rekap Pembelian</h3>
        <p class="text-muted small mb-0">Isi formulir berikut untuk mencatat transaksi pembelian baru.</p>
    </div>
    <a href="{{ route('pembelian.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Rekap
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data" class="form-pembelian">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Transaksi Manual (Opsional)</label>
                    <input type="text" name="kode_manual" class="form-control" value="{{ old('kode_manual') }}" placeholder="No. Invoice / Resi Toko">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Barang Pembelian <span class="text-danger">*</span></label>
                    <select name="nama_barang" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Barang --</option>
                        @foreach($barangs as $brg)
                        <option value="{{ $brg->nama_barang }}" {{ old('nama_barang') == $brg->nama_barang ? 'selected' : '' }}>
                            {{ $brg->nama_barang }}
                        </option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Toko Pembelian <span class="text-danger">*</span></label>
                    <select name="nama_toko" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Toko --</option>
                        @foreach($tokos as $tk)
                        <option value="{{ $tk->nama_toko }}" {{ old('nama_toko') == $tk->nama_toko ? 'selected' : '' }}>
                            {{ $tk->nama_toko }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Transaksi Beli Via <span class="text-danger">*</span></label>
                    <select id="selectViaTambah" class="form-select select-via-toggle" data-target="#inputViaTambah" required>
                        <option value="Tokopedia">Tokopedia</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                        <option value="TikTok">TikTok</option>
                        <option value="Lainnya">Lainnya (Ketik Manual)</option>
                    </select>
                    <input
                        type="text"
                        id="inputViaTambah"
                        name="via"
                        class="form-control mt-2 d-none"
                        value="Tokopedia"
                        placeholder="Ketik platform/via transaksi manual...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Beli <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_beli" class="form-control" value="{{ old('tanggal_beli', date('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Total Modal (Rp) <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        name="total_modal"
                        class="form-control input-rupiah"
                        value="{{ old('total_modal') }}"
                        placeholder="Misal: 8.500.000"
                        required
                        autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status Barang <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="Belum Ready" selected>⏳ Belum Ready</option>
                        <option value="Sudah Ready">✅ Sudah Ready</option>
                        <option value="Sudah Diambil">📦 Sudah Diambil</option>
                        <option value="Bermasalah">⚠️ Bermasalah</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Upload Lampiran (Bisa Pilih Banyak)</label>
                    <input type="file" name="file_lampiran[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                    <small class="text-muted">Tahan tombol <b>Ctrl</b> / <b>Shift</b> saat memilih file (JPG, PNG, PDF, maks 2MB/file)</small>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('pembelian.index') }}" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save"></i> Simpan Pembelian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle input via transaksi manual
        const selectVia = document.getElementById('selectViaTambah');
        const inputVia = document.getElementById('inputViaTambah');

        if (selectVia) {
            selectVia.addEventListener('change', function() {
                if (this.value === 'Lainnya') {
                    inputVia.classList.remove('d-none');
                    inputVia.value = '';
                    inputVia.focus();
                    inputVia.required = true;
                } else {
                    inputVia.classList.add('d-none');
                    inputVia.value = this.value;
                    inputVia.required = false;
                }
            });
        }

        // Format Ribuan (Titik) Realtime
        function formatRupiah(angka) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        document.querySelectorAll('.input-rupiah').forEach(function(input) {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        });

        document.querySelectorAll('.form-pembelian').forEach(function(form) {
            form.addEventListener('submit', function() {
                let rupiahInput = form.querySelector('.input-rupiah');
                if (rupiahInput) {
                    rupiahInput.value = rupiahInput.value.replace(/\./g, '');
                }
            });
        });
    });
</script>
@endsection