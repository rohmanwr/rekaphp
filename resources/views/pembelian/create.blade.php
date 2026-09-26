@extends('layouts.app')

@section('title', 'Tambah Rekap Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Tambah Rekap Pembelian (Multi Input)</h3>
        <p class="text-muted small mb-0">Isi formulir berikut untuk mencatat satu atau banyak transaksi pembelian sekaligus.</p>
    </div>
    <a href="{{ Route::has('pembelian.index') ? route('pembelian.index') : route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Rekap
    </a>
</div>

<form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data" id="formMultiPembelian">
    @csrf

    <!-- Container Baris Multi Input -->
    <div id="containerRows">
        <!-- Row Item Master 1 -->
        <div class="card border-0 shadow-sm mb-4 item-row" data-index="0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-bag-plus-fill me-2"></i>Item Transaksi #<span class="row-number">1</span></h6>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row d-none">
                    <i class="bi bi-trash"></i> Hapus Baris
                </button>
            </div>
            <div class="card-body p-4 pt-1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">No. Pesanan <span class="text-danger">*</span></label>
                        <input type="text" name="items[0][kode_manual]" class="form-control" placeholder="Contoh: INV/2026/001 / No. Invoice Toko" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama pada Alamat</label>
                        <input type="text" name="items[0][nama_alamat]" class="form-control" placeholder="Contoh: Budi (Penerima Paket)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Barang Pembelian <span class="text-danger">*</span></label>
                        <select name="items[0][nama_barang]" class="form-select select-barang" required>
                            <!-- Opsi diisi otomatis via JS -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Toko Pembelian <span class="text-danger">*</span></label>
                        <select name="items[0][nama_toko]" class="form-select select-toko" required>
                            <!-- Opsi diisi otomatis via JS -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Transaksi Beli Via <span class="text-danger">*</span></label>
                        <select class="form-select select-via-toggle" required>
                            <option value="Tokopedia" selected>Tokopedia</option>
                            <option value="Shopee">Shopee</option>
                            <option value="Lazada">Lazada</option>
                            <option value="TikTok">TikTok</option>
                            <option value="COD">COD</option>
                            <option value="Lainnya">Lainnya (Ketik Manual)</option>
                        </select>
                        <input type="text" name="items[0][via]" class="form-control mt-2 input-via-manual d-none" value="Tokopedia" placeholder="Ketik platform/via transaksi manual...">
                    </div>

                    <!-- Input Qty Khusus COD -->
                    <div class="col-md-6 d-none wrapper-qty-cod">
                        <label class="form-label fw-semibold text-primary">Jumlah Qty Barang (COD) <span class="text-danger">*</span></label>
                        <input type="number" name="items[0][qty]" class="form-control input-qty-cod" value="1" min="1" placeholder="Masukkan jumlah barang...">
                        <small class="text-muted">Jika diisi 3, maka sistem akan mencatat 3 barang berbeda secara otomatis.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Beli <span class="text-danger">*</span></label>
                        <input type="date" name="items[0][tanggal_beli]" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total Modal (Rp) <span class="text-danger">*</span></label>
                        <input type="text" name="items[0][total_modal]" class="form-control input-rupiah" placeholder="Misal: 8.500.000" required autocomplete="off">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status Barang <span class="text-danger">*</span></label>
                        <select name="items[0][status]" class="form-select" required>
                            <option value="Belum Ready" selected>⏳ Belum Ready</option>
                            <option value="Sudah Ready">✅ Sudah Ready</option>
                            <option value="Sudah Diambil">📦 Sudah Diambil</option>
                            <option value="Bermasalah">⚠️ Bermasalah</option>
                        </select>
                    </div>

                    <!-- Input IMEI / Serial dengan Tombol Scan Kamera -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">IMEI / Serial Number</label>
                        <div class="input-group">
                            <textarea name="items[0][detail_imei]" class="form-control target-imei" rows="1" placeholder="Tempel atau Scan IMEI di sini..."></textarea>
                            <button type="button" class="btn btn-outline-primary btn-scan-imei" title="Scan Barcode IMEI via Kamera">
                                <i class="bi bi-qr-code-scan"></i> Scan
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Upload Lampiran (Bisa Pilih Banyak)</label>
                        <input type="file" name="items[0][file_lampiran][]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Tambah Baris Multi Input -->
    <div class="mb-4">
        <button type="button" class="btn btn-outline-success w-100 py-2 fw-semibold" id="btnAddRow">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris Transaksi Baru
        </button>
    </div>

    <div class="card border-0 shadow-sm p-3">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ Route::has('pembelian.index') ? route('pembelian.index') : route('dashboard') }}" class="btn btn-light px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save"></i> Simpan Semua Pembelian
            </button>
        </div>
    </div>
</form>

<!-- Modal Scanner Kamera Barcode -->
<div class="modal fade" id="modalScanner" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-camera me-2"></i>Scan Barcode / QR IMEI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="reader" style="width: 100%; min-height: 250px; background: #f8f9fa;"></div>
                <small class="text-muted mt-2 d-block">Arahkan kamera ke barcode IMEI pada dus atau HP.</small>
            </div>
        </div>
    </div>
</div>

<!-- Import Pustaka Pemindai Barcode Html5Qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let rowCount = 1;
        let activeImeiInput = null;
        let html5QrcodeScanner = null;

        const containerRows = document.getElementById('containerRows');
        const btnAddRow = document.getElementById('btnAddRow');
        const modalScanner = new bootstrap.Modal(document.getElementById('modalScanner'));

        // Load Data Master dari Server ke JSON JS murni
        const barangsData = JSON.parse('{!! json_encode($barangs ?? []) !!}');
        const tokosData = JSON.parse('{!! json_encode($tokos ?? []) !!}');

        let barangOptionsHtml = '<option value="" selected disabled>-- Pilih Barang --</option>';
        if (Array.isArray(barangsData)) {
            barangsData.forEach(function(b) {
                barangOptionsHtml += '<option value="' + b.nama_barang + '">' + b.nama_barang + '</option>';
            });
        }

        let tokoOptionsHtml = '<option value="" selected disabled>-- Pilih Toko --</option>';
        if (Array.isArray(tokosData)) {
            tokosData.forEach(function(t) {
                tokoOptionsHtml += '<option value="' + t.nama_toko + '">' + t.nama_toko + '</option>';
            });
        }

        // Isi opsi baris pertama
        const firstBarangSelect = document.querySelector('.item-row[data-index="0"] .select-barang');
        const firstTokoSelect = document.querySelector('.item-row[data-index="0"] .select-toko');
        if (firstBarangSelect) firstBarangSelect.innerHTML = barangOptionsHtml;
        if (firstTokoSelect) firstTokoSelect.innerHTML = tokoOptionsHtml;

        // Format Rupiah
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
            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        // Bind Event listeners untuk setiap baris
        function bindRowEvents(row) {
            const selectVia = row.querySelector('.select-via-toggle');
            const inputViaManual = row.querySelector('.input-via-manual');
            const wrapperQtyCod = row.querySelector('.wrapper-qty-cod');
            const inputQtyCod = row.querySelector('.input-qty-cod');
            const inputRupiah = row.querySelector('.input-rupiah');
            const btnScan = row.querySelector('.btn-scan-imei');
            const targetImei = row.querySelector('.target-imei');

            if (selectVia) {
                selectVia.addEventListener('change', function() {
                    const val = this.value;
                    inputViaManual.classList.add('d-none');
                    inputViaManual.required = false;
                    wrapperQtyCod.classList.add('d-none');
                    inputQtyCod.required = false;

                    if (val === 'COD') {
                        wrapperQtyCod.classList.remove('d-none');
                        inputQtyCod.required = true;
                        inputViaManual.value = 'COD';
                    } else if (val === 'Lainnya') {
                        inputViaManual.classList.remove('d-none');
                        inputViaManual.value = '';
                        inputViaManual.required = true;
                        inputViaManual.focus();
                    } else {
                        inputViaManual.value = val;
                    }
                });
            }

            if (inputRupiah) {
                inputRupiah.addEventListener('keyup', function() {
                    this.value = formatRupiah(this.value);
                });
            }

            if (btnScan) {
                btnScan.addEventListener('click', function() {
                    activeImeiInput = targetImei;
                    modalScanner.show();
                });
            }

            const btnRemove = row.querySelector('.btn-remove-row');
            if (btnRemove) {
                btnRemove.addEventListener('click', function() {
                    row.remove();
                    updateRowNumbers();
                });
            }
        }

        function updateRowNumbers() {
            const rows = containerRows.querySelectorAll('.item-row');
            rows.forEach((r, idx) => {
                r.querySelector('.row-number').textContent = idx + 1;
                const btnRemove = r.querySelector('.btn-remove-row');
                if (rows.length > 1) {
                    btnRemove.classList.remove('d-none');
                } else {
                    btnRemove.classList.add('d-none');
                }
            });
        }

        const firstRow = containerRows.querySelector('.item-row');
        if (firstRow) bindRowEvents(firstRow);

        // Tambah Baris Baru
        if (btnAddRow) {
            btnAddRow.addEventListener('click', function() {
                const index = rowCount++;
                const template = `
                <div class="card border-0 shadow-sm mb-4 item-row" data-index="${index}">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-bag-plus-fill me-2"></i>Item Transaksi #<span class="row-number">${index + 1}</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                            <i class="bi bi-trash"></i> Hapus Baris
                        </button>
                    </div>
                    <div class="card-body p-4 pt-1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">No. Pesanan <span class="text-danger">*</span></label>
                                <input type="text" name="items[${index}][kode_manual]" class="form-control" placeholder="Contoh: INV/2026/001 / No. Invoice Toko" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama pada Alamat</label>
                                <input type="text" name="items[${index}][nama_alamat]" class="form-control" placeholder="Contoh: Budi (Penerima Paket)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Barang Pembelian <span class="text-danger">*</span></label>
                                <select name="items[${index}][nama_barang]" class="form-select select-barang" required>
                                    ${barangOptionsHtml}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Toko Pembelian <span class="text-danger">*</span></label>
                                <select name="items[${index}][nama_toko]" class="form-select select-toko" required>
                                    ${tokoOptionsHtml}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Transaksi Beli Via <span class="text-danger">*</span></label>
                                <select class="form-select select-via-toggle" required>
                                    <option value="Tokopedia" selected>Tokopedia</option>
                                    <option value="Shopee">Shopee</option>
                                    <option value="Lazada">Lazada</option>
                                    <option value="TikTok">TikTok</option>
                                    <option value="COD">COD</option>
                                    <option value="Lainnya">Lainnya (Ketik Manual)</option>
                                </select>
                                <input type="text" name="items[${index}][via]" class="form-control mt-2 input-via-manual d-none" value="Tokopedia" placeholder="Ketik platform/via transaksi manual...">
                            </div>
                            <div class="col-md-6 d-none wrapper-qty-cod">
                                <label class="form-label fw-semibold text-primary">Jumlah Qty Barang (COD) <span class="text-danger">*</span></label>
                                <input type="number" name="items[${index}][qty]" class="form-control input-qty-cod" value="1" min="1" placeholder="Masukkan jumlah barang...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Beli <span class="text-danger">*</span></label>
                                <input type="date" name="items[${index}][tanggal_beli]" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Total Modal (Rp) <span class="text-danger">*</span></label>
                                <input type="text" name="items[${index}][total_modal]" class="form-control input-rupiah" placeholder="Misal: 8.500.000" required autocomplete="off">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Barang <span class="text-danger">*</span></label>
                                <select name="items[${index}][status]" class="form-select" required>
                                    <option value="Belum Ready" selected>⏳ Belum Ready</option>
                                    <option value="Sudah Ready">✅ Sudah Ready</option>
                                    <option value="Sudah Diambil">📦 Sudah Diambil</option>
                                    <option value="Bermasalah">⚠️ Bermasalah</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">IMEI / Serial Number</label>
                                <div class="input-group">
                                    <textarea name="items[${index}][detail_imei]" class="form-control target-imei" rows="1" placeholder="Tempel atau Scan IMEI di sini..."></textarea>
                                    <button type="button" class="btn btn-outline-primary btn-scan-imei" title="Scan Barcode IMEI via Kamera">
                                        <i class="bi bi-qr-code-scan"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Upload Lampiran (Bisa Pilih Banyak)</label>
                                <input type="file" name="items[${index}][file_lampiran][]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple>
                            </div>
                        </div>
                    </div>
                </div>`;

                containerRows.insertAdjacentHTML('beforeend', template);
                const newRow = containerRows.lastElementChild;
                bindRowEvents(newRow);
                updateRowNumbers();
            });
        }

        // Modal Scanner
        const modalElement = document.getElementById('modalScanner');
        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', function() {
                if (!html5QrcodeScanner) {
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 150
                        }
                    }, false);
                    html5QrcodeScanner.render(onScanSuccess);
                }
            });

            modalElement.addEventListener('hidden.bs.modal', function() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                }
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            if (activeImeiInput) {
                const currentVal = activeImeiInput.value.trim();
                activeImeiInput.value = currentVal ? currentVal + "\n" + decodedText : decodedText;
            }
            modalScanner.hide();
        }

        const formElement = document.getElementById('formMultiPembelian');
        if (formElement) {
            formElement.addEventListener('submit', function() {
                document.querySelectorAll('.input-rupiah').forEach(function(input) {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        }
    });
</script>
@endsection