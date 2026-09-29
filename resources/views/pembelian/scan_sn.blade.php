@extends('layouts.app')

@section('title', 'Scan Serial Number (SN / Teks)')

@section('content')
<!-- Library Scanner Barcode HTML5 & OCR Tesseract.js -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-camera-fill me-2"></i> Scan Serial Number (SN / Teks)</h5>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-sm btn-light fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4 text-center">

                    <!-- Petunjuk -->
                    <div class="alert alert-info text-start border-0 shadow-sm mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
                            <div>
                                <strong>Pilih Mode Pemindaian:</strong><br>
                                Gunakan <strong>Barcode Only</strong> jika hanya ingin scan barcode, <strong>Hanya Teks (OCR)</strong> untuk membaca tulisan <code>Serial No.</code>, atau <strong>Mode Otomatis</strong> untuk keduanya.
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Mode Scan -->
                    <div class="btn-group w-100 mb-3" role="group" id="scanModeGroup">
                        <input type="radio" class="btn-check" name="scanMode" id="modeAuto" value="auto" checked>
                        <label class="btn btn-outline-primary fw-semibold" for="modeAuto"><i class="bi bi-magic me-1"></i> Otomatis (Barcode + Teks)</label>

                        <input type="radio" class="btn-check" name="scanMode" id="modeBarcode" value="barcode">
                        <label class="btn btn-outline-primary fw-semibold" for="modeBarcode"><i class="bi bi-barcode me-1"></i> Barcode Only</label>

                        <input type="radio" class="btn-check" name="scanMode" id="modeOcr" value="ocr">
                        <label class="btn btn-outline-primary fw-semibold" for="modeOcr"><i class="bi bi-text-paragraph me-1"></i> Hanya Teks (OCR)</label>
                    </div>

                    <!-- Area Live Kamera dengan Overlay Laser -->
                    <div class="position-relative my-3 rounded-3 overflow-hidden border bg-dark" style="min-height: 300px;">
                        <!-- Frame Panduan Laser Tengah -->
                        <div id="scanOverlay" class="d-none position-absolute top-50 start-50 translate-middle border border-2 border-warning rounded-3 w-85 h-35" style="z-index: 10; pointer-events: none; box-shadow: 0 0 15px rgba(255,193,7,0.7);">
                            <span id="overlayText" class="badge bg-warning text-dark position-absolute top-0 start-50 translate-middle-x mt-1" style="font-size: 0.7rem;">Arahkan Barcode / Tulisan SN Ke Sini</span>
                        </div>

                        <div id="reader" style="width: 100%;"></div>
                        <canvas id="ocrCanvas" class="d-none"></canvas>
                    </div>

                    <!-- Option Switch: Hapus Prefix 'S' -->
                    <div class="form-check form-switch d-inline-block mb-3 text-start">
                        <input class="form-check-input" type="checkbox" id="trimPrefixS" checked>
                        <label class="form-check-label fw-semibold small text-muted" for="trimPrefixS">
                            Hapus Awalan 'S' / '(S)' (Contoh: <code>(S) Serial No. CMHCQ94YFG</code> ➔ <code>CMHCQ94YFG</code>)
                        </label>
                    </div>

                    <!-- Tombol Kontrol Kamera -->
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <button type="button" id="btnStartScan" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-camera-fill me-1"></i> Buka Kamera & Scan
                        </button>
                        <button type="button" id="btnStopScan" class="btn btn-danger px-4 fw-bold d-none">
                            <i class="bi bi-stop-circle me-1"></i> Hentikan Kamera
                        </button>
                    </div>

                    <!-- Status Indicator OCR -->
                    <div id="ocrStatus" class="small text-muted mb-2 d-none">
                        <div class="spinner-border spinner-border-sm text-primary me-1" role="status"></div>
                        <span id="ocrStatusText">Menganalisis teks pada dus...</span>
                    </div>

                    <!-- Output Hasil Scan & Tombol Aksi -->
                    <div class="card bg-light border-0 p-3 text-start">
                        <label class="form-label fw-bold text-dark fs-6">
                            <i class="bi bi-check-circle-fill text-success me-1"></i> Hasil Serial Number Terdeteksi:
                        </label>
                        <div class="input-group input-group-lg mb-2">
                            <input type="text" id="resultSN" class="form-control font-monospace fw-bold text-primary fs-4 bg-white" placeholder="Menunggu scan..." readonly>
                            <button class="btn btn-primary px-3 fw-bold" type="button" onclick="copyHasilSN()">
                                <i class="bi bi-clipboard me-1"></i> Salin
                            </button>
                        </div>

                        <!-- Tombol Cek Garansi / Aktivasi Apple -->
                        <div class="d-grid mt-2">
                            <button id="btnCheckApple" class="btn btn-dark btn-lg fw-bold d-none" type="button" onclick="checkAppleCoverage()">
                                <i class="bi bi-apple me-2 text-warning"></i> Check Aktivasi di Apple Coverage
                            </button>
                        </div>

                        <!-- Log Pembacaan -->
                        <div class="mt-3">
                            <small class="text-muted fw-semibold">Riwayat Deteksi:</small>
                            <ul id="scanLog" class="list-group list-group-flush small font-monospace mt-1 rounded border" style="max-height: 140px; overflow-y: auto;">
                                <li class="list-group-item text-muted text-center py-2">Belum ada data yang terbaca.</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let html5QrCode = null;
    let isScanning = false;
    let ocrInterval = null;
    let workerTesseract = null;

    const btnStart = document.getElementById('btnStartScan');
    const btnStop = document.getElementById('btnStopScan');
    const inputResult = document.getElementById('resultSN');
    const btnCheckApple = document.getElementById('btnCheckApple');
    const scanLog = document.getElementById('scanLog');
    const overlay = document.getElementById('scanOverlay');
    const ocrStatus = document.getElementById('ocrStatus');
    const ocrStatusText = document.getElementById('ocrStatusText');
    const trimPrefixS = document.getElementById('trimPrefixS');

    function getSelectedMode() {
        const selected = document.querySelector('input[name="scanMode"]:checked');
        return selected ? selected.value : 'auto';
    }

    // Inisialisasi Tesseract OCR Worker
    async function initTesseract() {
        const mode = getSelectedMode();
        if (mode !== 'barcode' && !workerTesseract) {
            ocrStatusText.innerText = "Menyiapkan mesin OCR pembaca teks...";
            ocrStatus.classList.remove('d-none');
            workerTesseract = await Tesseract.createWorker('eng');
            ocrStatus.classList.add('d-none');
        }
    }

    // Fungsi Utama Memulai Scanner
    async function startScanner() {
        if (isScanning) return;

        btnStart.disabled = true;
        const currentMode = getSelectedMode();

        if (currentMode !== 'barcode') {
            await initTesseract();
        }
        btnStart.disabled = false;

        html5QrCode = new Html5Qrcode("reader");
        const config = {
            fps: 15,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                return {
                    width: Math.floor(viewfinderWidth * 0.85),
                    height: Math.floor(viewfinderHeight * 0.35)
                };
            },
            aspectRatio: 1.0,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            }
        };

        html5QrCode.start({
                facingMode: "environment"
            },
            config,
            onBarcodeSuccess,
            onScanFailure
        ).then(() => {
            isScanning = true;
            btnStart.classList.add('d-none');
            btnStop.classList.remove('d-none');
            overlay.classList.remove('d-none');

            if (currentMode === 'auto' || currentMode === 'ocr') {
                ocrInterval = setInterval(runOcrScan, 1500);
            }
        }).catch(err => {
            alert("Gagal mengakses kamera: " + err);
        });
    }

    // 1. Deteksi Barcode
    function onBarcodeSuccess(decodedText) {
        const currentMode = getSelectedMode();
        if (currentMode === 'ocr') return;

        processDetectedText(decodedText.trim(), 'Barcode');
    }

    // 2. Deteksi OCR Teks
    async function runOcrScan() {
        const currentMode = getSelectedMode();
        if (currentMode === 'barcode') return;
        if (!isScanning || !workerTesseract) return;

        const videoElement = document.querySelector("#reader video");
        if (!videoElement || videoElement.readyState !== 4) return;

        const canvas = document.getElementById("ocrCanvas");
        const ctx = canvas.getContext("2d");

        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        ocrStatus.classList.remove('d-none');
        ocrStatusText.innerText = "Membaca teks tulisan...";

        try {
            const result = await workerTesseract.recognize(canvas);
            const rawText = result.data.text;

            ocrStatus.classList.add('d-none');

            if (rawText) {
                const lines = rawText.split('\n');
                for (let line of lines) {
                    let cleanLine = line.trim();
                    if (cleanLine.length > 5) {
                        processDetectedText(cleanLine, 'Teks OCR');
                    }
                }
            }
        } catch (e) {
            ocrStatus.classList.add('d-none');
        }
    }

    // Logika Pemrosesan Teks & Ekstraksi SN
    function processDetectedText(text, source) {
        if (/EID/i.test(text) || /^8904/.test(text) || /^35\d{13}/.test(text) || /^86\d{13}/.test(text)) {
            addLogItem(text, 'Diabaikan (EID/IMEI)', 'bg-secondary');
            return;
        }

        let extractedSN = null;

        let matchSerialNo = text.match(/(?:Serial\s*No\.?|SN\b)[:\s]*([A-Za-z0-9]+)/i);
        if (matchSerialNo && matchSerialNo[1]) {
            extractedSN = matchSerialNo[1];
        } else if (/^[Ss][A-Za-z0-9]{7,15}$/.test(text)) {
            extractedSN = text;
        }

        if (extractedSN) {
            let finalSN = extractedSN;

            if (trimPrefixS.checked && /^[Ss][A-Za-z0-9]+$/.test(finalSN)) {
                finalSN = finalSN.substring(1);
            }

            inputResult.value = finalSN;

            // Tampilkan tombol Check Apple Coverage
            btnCheckApple.classList.remove('d-none');

            if (navigator.vibrate) {
                navigator.vibrate([100, 50, 100]);
            }

            addLogItem(text + " ➔ " + finalSN, 'Match SN (' + source + ')', 'bg-success');
            stopScanner();

            alert("Serial Number Terdeteksi dari " + source + ":\n" + finalSN + "\n\nKlik tombol 'Check Aktivasi di Apple Coverage' untuk memeriksa status garansi.");
        } else {
            if (text.length > 4) {
                addLogItem(text, 'Diabaikan', 'bg-light text-dark');
            }
        }
    }

    function onScanFailure(error) {
        // Abaikan frame kegagalan biasa
    }

    // Menghentikan Kamera & Worker OCR
    function stopScanner() {
        if (ocrInterval) {
            clearInterval(ocrInterval);
            ocrInterval = null;
        }

        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                isScanning = false;
                btnStart.classList.remove('d-none');
                btnStop.classList.add('d-none');
                overlay.classList.add('d-none');
                ocrStatus.classList.add('d-none');
            }).catch(err => console.error(err));
        }
    }

    // Log Riwayat
    function addLogItem(text, label, badgeClass) {
        if (scanLog.children.length === 1 && scanLog.children[0].classList.contains('text-center')) {
            scanLog.innerHTML = '';
        }

        let li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center py-1 px-2';
        li.innerHTML = `<span><span class="badge ${badgeClass} me-2">${label}</span> <code>${text}</code></span> <span class="text-muted fs-7">${new Date().toLocaleTimeString('id-ID')}</span>`;

        scanLog.insertBefore(li, scanLog.firstChild);
    }

    // Copy ke Clipboard
    function copyHasilSN() {
        if (!inputResult.value) {
            alert("Belum ada Serial Number yang terdeteksi!");
            return;
        }

        inputResult.select();
        inputResult.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(inputResult.value).then(() => {
            alert("Serial Number " + inputResult.value + " berhasil disalin!");
        });
    }

    // Fungsi Buka Pengecekan Aktivasi Apple di Tab Baru & Salin SN Otomatis
    function checkAppleCoverage() {
        const sn = inputResult.value;
        if (!sn) {
            alert("Serial Number belum tersedia!");
            return;
        }

        // Salin SN ke clipboard agar tinggal di-paste pengguna di web Apple
        navigator.clipboard.writeText(sn).then(() => {
            alert("Serial Number (" + sn + ") berhasil disalin ke clipboard!\n\nAnda akan diarahkan ke website Apple Coverage, silakan PASTE (Tempel) Serial Number pada kolom yang tersedia.");
            // Buka halaman Apple Coverage Indonesia di tab baru
            window.open("https://checkcoverage.apple.com/?locale=in_ID", "_blank");
        }).catch(() => {
            window.open("https://checkcoverage.apple.com/?locale=in_ID", "_blank");
        });
    }

    btnStart.addEventListener('click', startScanner);
    btnStop.addEventListener('click', stopScanner);
    window.addEventListener('beforeunload', stopScanner);
</script>
@endsection