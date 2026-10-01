@extends('layouts.app')

@section('title', 'Tambah Rekap Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Tambah Rekap Pembelian (Batch Toko)</h3>
        <p class="text-muted small mb-0">Input 1 data toko & barang untuk mencatat banyak nomor pesanan sekaligus.</p>
    </div>
    <a href="{{ Route::has('pembelian.index') ? route('pembelian.index') : route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Rekap
    </a>
</div>

<form action="{{ route('pembelian.store') }}" method="POST" enctype="multipart/form-data" id="formBatchPembelian">
    @csrf

    <!-- Form Container Utama untuk Mengirim Array Items yang Siap Diolah Backend Laravel -->
    <div id="hiddenSubmittedInputs"></div>

    <!-- CARD MASTER (DATA UTAMA UTK 1 TOKO & BARANG) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Barang Pembelian <span class="text-danger">*</span></label>
                    <select id="master_nama_barang" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Barang --</option>
                        @foreach ($barangs as $barang)
                        <option value="{{ $barang->nama_barang }}" data-harga="{{ (int) $barang->harga_jual }}">{{ $barang->nama_barang }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Toko Pembelian <span class="text-danger">*</span></label>
                    <select id="master_nama_toko" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Toko --</option>
                        @foreach ($tokos as $toko)
                        <option value="{{ $toko->nama_toko }}">{{ $toko->nama_toko }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Transaksi Beli Via <span class="text-danger">*</span></label>
                    <select id="select_master_via" class="form-select" required>
                        <option value="Tokopedia" selected>Tokopedia</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Lazada">Lazada</option>
                        <option value="TikTok">TikTok</option>
                        <option value="COD">COD</option>
                        <option value="Lainnya">Lainnya (Ketik Manual)</option>
                    </select>
                    <input type="text" id="input_master_via_manual" class="form-control mt-2 d-none" value="Tokopedia" placeholder="Ketik platform/via transaksi manual...">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Trader</label>
                    <input type="text" id="master_nama_trader" class="form-control" placeholder="Masukkan nama trader (opsional)" autocomplete="off">
                </div>

                <!-- Input Qty Khusus Transaksi COD -->
                <div class="col-md-6 d-none" id="wrapper_master_qty_cod">
                    <label class="form-label fw-semibold text-primary">Jumlah Qty Barang (COD) <span class="text-danger">*</span></label>
                    <input type="number" id="master_qty_cod" class="form-control" value="1" min="1" placeholder="Masukkan jumlah barang...">
                    <small class="text-muted">Jika diisi 3, maka daftar nomor pesanan unit di bawah otomatis bertambah menjadi 3 baris.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Beli <span class="text-danger">*</span></label>
                    <input type="date" id="master_tanggal_beli" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Titipan <span class="text-danger">*</span></label>
                    <select id="master_titipan" class="form-select" required>
                        <option value="Tidak" selected>Tidak</option>
                        <option value="Ya">Ya</option>
                        <option value="Ya, (tidak ambil untung)">Ya, (tidak ambil untung)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Total Modal (Rp) <span class="text-danger">*</span></label>
                    <input type="text" id="master_total_modal" class="form-control input-rupiah" placeholder="Misal: 12.000.000" required autocomplete="off">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status Barang <span class="text-danger">*</span></label>
                    <select id="master_status" class="form-select" required>
                        <option value="Belum Ready" selected>⏳ Belum Ready</option>
                        <option value="Sudah Ready">✅ Sudah Ready</option>
                        <option value="Sudah Diambil">📦 Sudah Diambil</option>
                        <option value="Bermasalah">⚠️ Bermasalah</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTAINER DAFTAR NOMOR PESANAN MULTI INPUT -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom-0">
            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-list-check me-2"></i>Daftar Nomor Pesanan Unit</h6>
        </div>
        <div class="card-body p-4 pt-0">
            <div id="containerSubRows">
                <!-- Baris Pesanan 1 (Default) -->
                <div class="sub-row border rounded p-3 mb-3 bg-light position-relative" data-index="0">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-secondary small">Pesanan #<span class="row-number">1</span></span>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-subrow d-none">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">No. Pesanan (Opsional)</label>
                            <input type="text" class="form-control input-kode-manual" placeholder="Nomor Resi / Invoice Toko">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Nama pada Alamat (Opsional)</label>
                            <input type="text" class="form-control input-nama-alamat" placeholder="Penerima / Keterangan Alamat">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">IMEI / Serial Number</label>
                            <div class="input-group">
                                <input type="text" id="imei_input_0" class="form-control font-monospace input-detail-imei" placeholder="Ketik / Scan IMEI...">
                                <button type="button" class="btn btn-outline-primary btn-scan-imei" data-target="imei_input_0" title="Scan Barcode IMEI via Kamera">
                                    <i class="bi bi-qr-code-scan"></i> Scan
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small mb-1">Upload Lampiran (Bisa Banyak File)</label>
                            <input type="file" class="form-control input-lampiran" accept=".jpg,.jpeg,.png,.pdf" multiple>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Tambah Baris Nomor Pesanan Baru -->
            <button type="button" class="btn btn-outline-success w-100 py-2 fw-semibold mt-2" id="btnAddSubRow">
                <i class="bi bi-plus-circle-fill me-1"></i> Tambah Baris Nomor Pesanan Baru
            </button>
        </div>
    </div>

    <!-- Submit Section -->
    <div class="card border-0 shadow-sm p-3">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ Route::has('pembelian.index') ? route('pembelian.index') : route('dashboard') }}" class="btn btn-light px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-save me-1"></i> Simpan Semua Pembelian
            </button>
        </div>
    </div>
</form>

<!-- Modal Scanner Kamera Barcode -->
<div class="modal fade" id="modalScanner" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-camera me-2"></i>Scan Barcode / QR IMEI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-info py-2 small mb-2">
                    <i class="bi bi-info-circle"></i> Arahkan kamera ke barcode/QR Code IMEI pada dus HP. Barcode akan otomatis terdeteksi.
                </div>
                <div id="reader" class="border rounded overflow-hidden" style="width: 100%; max-width: 450px; margin: 0 auto; min-height: 250px; background-color: #000;"></div>
                <button type="button" class="btn btn-sm btn-secondary mt-3 px-3" data-bs-dismiss="modal">Tutup Kamera</button>
            </div>
        </div>
    </div>
</div>

<!-- Import Pustaka Pemindai Barcode Html5Qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let subRowCount = 1;
        let currentTargetId = null;
        let html5QrCodeScannerInstance = null;
        let scanLocked = false;

        const containerSubRows = document.getElementById('containerSubRows');
        const btnAddSubRow = document.getElementById('btnAddSubRow');
        const selectMasterVia = document.getElementById('select_master_via');
        const inputMasterViaManual = document.getElementById('input_master_via_manual');
        const wrapperMasterQtyCod = document.getElementById('wrapper_master_qty_cod');
        const masterQtyCod = document.getElementById('master_qty_cod');
        const masterModalInput = document.getElementById('master_total_modal');
        const masterTitipan = document.getElementById('master_titipan');
        const masterBarangSelect = document.getElementById('master_nama_barang');

        function syncMasterModal() {
            const usesMasterPrice = masterTitipan.value === 'Ya, (tidak ambil untung)';
            masterModalInput.readOnly = usesMasterPrice;
            if (usesMasterPrice) {
                const selectedBarang = masterBarangSelect.selectedOptions[0];
                masterModalInput.value = selectedBarang?.dataset.harga ?
                    formatRupiah(selectedBarang.dataset.harga) :
                    '';
            }
        }

        masterTitipan.addEventListener('change', syncMasterModal);
        masterBarangSelect.addEventListener('change', syncMasterModal);

        const modalElement = document.getElementById('modalScanner');
        const modalScanner = new bootstrap.Modal(modalElement);

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

        if (masterModalInput) {
            masterModalInput.addEventListener('keyup', function() {
                this.value = formatRupiah(this.value);
            });
        }

        // Toggle Transaksi Beli Via (COD & Lainnya)
        if (selectMasterVia) {
            selectMasterVia.addEventListener('change', function() {
                const val = this.value;
                inputMasterViaManual.classList.add('d-none');
                wrapperMasterQtyCod.classList.add('d-none');

                if (val === 'COD') {
                    wrapperMasterQtyCod.classList.remove('d-none');
                    inputMasterViaManual.value = 'COD';
                    // Auto atur baris sesuai nilai Qty COD yang tertera
                    if (masterQtyCod) adjustSubRowsByQty(parseInt(masterQtyCod.value) || 1);
                } else if (val === 'Lainnya') {
                    inputMasterViaManual.classList.remove('d-none');
                    inputMasterViaManual.value = '';
                    inputMasterViaManual.focus();
                } else {
                    inputMasterViaManual.value = val;
                }
            });
        }

        // Auto Atur Jumlah Baris Pesanan Berdasarkan Input Qty COD
        function adjustSubRowsByQty(targetQty) {
            if (targetQty < 1) targetQty = 1;
            let currentRowsCount = containerSubRows.querySelectorAll('.sub-row').length;

            if (targetQty > currentRowsCount) {
                let needToAdd = targetQty - currentRowsCount;
                for (let i = 0; i < needToAdd; i++) {
                    addNewSubRow();
                }
            } else if (targetQty < currentRowsCount) {
                let needToRemove = currentRowsCount - targetQty;
                let rows = containerSubRows.querySelectorAll('.sub-row');
                for (let i = currentRowsCount - 1; i >= currentRowsCount - needToRemove; i--) {
                    if (rows[i]) rows[i].remove();
                }
                updateSubRowNumbers();
            }
        }

        if (masterQtyCod) {
            masterQtyCod.addEventListener('input', function() {
                let val = parseInt(this.value) || 1;
                adjustSubRowsByQty(val);
            });
        }

        // Matikan Scanner Kamera
        async function stopScanner() {
            if (html5QrCodeScannerInstance) {
                try {
                    await html5QrCodeScannerInstance.stop();
                } catch (e) {}
                try {
                    html5QrCodeScannerInstance.clear();
                } catch (e) {}
                html5QrCodeScannerInstance = null;
            }
            const readerDiv = document.getElementById('reader');
            if (readerDiv) readerDiv.innerHTML = '';
        }

        // Jalankan Scanner Kamera
        async function startScanner() {
            await stopScanner();
            scanLocked = false;

            html5QrCodeScannerInstance = new Html5Qrcode("reader");
            const config = {
                fps: 15,
                qrbox: function(w, h) {
                    return {
                        width: Math.floor(w * 0.8),
                        height: Math.floor(h * 0.5)
                    };
                },
                aspectRatio: 1.0,
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                }
            };

            html5QrCodeScannerInstance.start({
                    facingMode: "environment"
                },
                config,
                function(decodedText) {
                    if (scanLocked) return;
                    scanLocked = true;

                    if (currentTargetId) {
                        const inputEl = document.getElementById(currentTargetId);
                        if (inputEl) inputEl.value = decodedText;
                    }

                    if (navigator.vibrate) navigator.vibrate(100);
                    modalScanner.hide();
                },
                function(errorMessage) {}
            ).catch(err => {
                alert("Gagal mengakses kamera: " + err);
                modalScanner.hide();
            });
        }

        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', function() {
                startScanner();
            });
            modalElement.addEventListener('hidden.bs.modal', function() {
                stopScanner();
                currentTargetId = null;
            });
        }

        function bindSubRowEvents(row) {
            const btnScan = row.querySelector('.btn-scan-imei');
            if (btnScan) {
                btnScan.addEventListener('click', function() {
                    currentTargetId = this.getAttribute('data-target');
                    modalScanner.show();
                });
            }

            const btnRemove = row.querySelector('.btn-remove-subrow');
            if (btnRemove) {
                btnRemove.addEventListener('click', function() {
                    row.remove();
                    updateSubRowNumbers();
                    // Update sync angka qty COD jika mode COD aktif
                    if (selectMasterVia.value === 'COD' && masterQtyCod) {
                        masterQtyCod.value = containerSubRows.querySelectorAll('.sub-row').length;
                    }
                });
            }
        }

        function updateSubRowNumbers() {
            const rows = containerSubRows.querySelectorAll('.sub-row');
            rows.forEach((r, idx) => {
                r.querySelector('.row-number').textContent = idx + 1;
                const btnRemove = r.querySelector('.btn-remove-subrow');
                if (rows.length > 1) {
                    btnRemove.classList.remove('d-none');
                } else {
                    btnRemove.classList.add('d-none');
                }
            });
        }

        function addNewSubRow() {
            const index = subRowCount++;
            const uniqueInputId = `imei_input_${index}`;
            const template = `
            <div class="sub-row border rounded p-3 mb-3 bg-light position-relative" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-secondary small">Pesanan #<span class="row-number">${index + 1}</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-subrow">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">No. Pesanan (Opsional)</label>
                        <input type="text" class="form-control input-kode-manual" placeholder="Nomor Resi / Invoice Toko">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">Nama pada Alamat (Opsional)</label>
                        <input type="text" class="form-control input-nama-alamat" placeholder="Penerima / Keterangan Alamat">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">IMEI / Serial Number</label>
                        <div class="input-group">
                            <input type="text" id="${uniqueInputId}" class="form-control font-monospace input-detail-imei" placeholder="Ketik / Scan IMEI...">
                            <button type="button" class="btn btn-outline-primary btn-scan-imei" data-target="${uniqueInputId}" title="Scan Barcode IMEI via Kamera">
                                <i class="bi bi-qr-code-scan"></i> Scan
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small mb-1">Upload Lampiran (Bisa Banyak File)</label>
                        <input type="file" class="form-control input-lampiran" accept=".jpg,.jpeg,.png,.pdf" multiple>
                    </div>
                </div>
            </div>`;

            containerSubRows.insertAdjacentHTML('beforeend', template);
            const newRow = containerSubRows.lastElementChild;
            bindSubRowEvents(newRow);
            updateSubRowNumbers();
        }

        const firstSubRow = containerSubRows.querySelector('.sub-row');
        if (firstSubRow) bindSubRowEvents(firstSubRow);

        // Event Tambah Baris Manual via Tombol
        if (btnAddSubRow) {
            btnAddSubRow.addEventListener('click', function() {
                addNewSubRow();
                if (selectMasterVia.value === 'COD' && masterQtyCod) {
                    masterQtyCod.value = containerSubRows.querySelectorAll('.sub-row').length;
                }
            });
        }

        // Form Submit Handler: Replikasi Master Data ke Setiap Sub-Row Pesanan
        const formElement = document.getElementById('formBatchPembelian');
        if (formElement) {
            formElement.addEventListener('submit', function(e) {
                const masterNamaBarang = document.getElementById('master_nama_barang').value;
                const masterTitipanValue = masterTitipan.value;
                const masterNamaToko = document.getElementById('master_nama_toko').value;
                const masterNamaTrader = document.getElementById('master_nama_trader').value;
                const masterVia = selectMasterVia.value === 'Lainnya' ? inputMasterViaManual.value : selectMasterVia.value;
                const masterTanggalBeli = document.getElementById('master_tanggal_beli').value;
                const masterTotalModal = masterModalInput.value.replace(/\./g, '');
                const masterStatus = document.getElementById('master_status').value;

                const subRows = containerSubRows.querySelectorAll('.sub-row');
                const hiddenContainer = document.getElementById('hiddenSubmittedInputs');
                hiddenContainer.innerHTML = ''; // Clear container

                subRows.forEach((row, i) => {
                    const kodeManual = row.querySelector('.input-kode-manual').value;
                    const namaAlamat = row.querySelector('.input-nama-alamat').value;
                    const detailImei = row.querySelector('.input-detail-imei').value;
                    const lampiranInput = row.querySelector('.input-lampiran');

                    // Set Name attribute dinamis agar masuk array $request->items di Controller
                    hiddenContainer.insertAdjacentHTML('beforeend', `
                        <input type="hidden" name="items[${i}][nama_barang]" value="${masterNamaBarang}">
                        <input type="hidden" name="items[${i}][nama_toko]" value="${masterNamaToko}">
                        <input type="hidden" name="items[${i}][via]" value="${masterVia}">
                        <input type="hidden" name="items[${i}][nama_trader]" value="${masterNamaTrader}">
                        <input type="hidden" name="items[${i}][tanggal_beli]" value="${masterTanggalBeli}">
                        <input type="hidden" name="items[${i}][total_modal]" value="${masterTotalModal}">
                        <input type="hidden" name="items[${i}][titipan]" value="${masterTitipanValue}">
                        <input type="hidden" name="items[${i}][status]" value="${masterStatus}">

                        <input type="hidden" name="items[${i}][kode_manual]" value="${kodeManual}">
                        <input type="hidden" name="items[${i}][nama_alamat]" value="${namaAlamat}">
                        <input type="hidden" name="items[${i}][detail_imei]" value="${detailImei}">
                    `);

                    // Pindahkan attribute name file lampiran agar dikirim dengan index presisi
                    if (lampiranInput) {
                        lampiranInput.setAttribute('name', `items[${i}][file_lampiran][]`);
                    }
                });
            });
        }
    });
</script>
@endsection