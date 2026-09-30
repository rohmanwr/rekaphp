@extends('layouts.app')

@section('title', 'Retur Barang')

@section('content')
<style>
    .return-page-header {
        border-bottom: 1px solid #dbe4e2;
        padding-bottom: 1rem;
    }

    .return-kpi {
        border: 1px solid #dbe4e2;
        border-top: 3px solid #18786e;
        border-radius: 6px;
        background: #fff;
        min-height: 112px;
    }

    .return-kpi .kpi-value {
        color: #203432;
        font-size: 1.4rem;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .return-form-card,
    .return-ledger-card {
        border: 1px solid #dbe4e2;
        border-radius: 6px;
        box-shadow: 0 3px 12px rgba(27, 47, 44, 0.05);
    }

    .return-summary {
        background: #f1f7f5;
        border-left: 3px solid #18786e;
    }

    .return-imei {
        font-family: monospace;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .return-mobile-entry {
        border: 1px solid #dbe4e2;
        border-left: 3px solid #18786e;
        border-radius: 6px;
        background: #fff;
    }

    @media (max-width: 767.98px) {
        .return-page-header h1 {
            font-size: 1.35rem;
        }

        .return-kpi {
            min-height: 96px;
        }

        .return-kpi .kpi-value {
            font-size: 1.15rem;
        }
    }
</style>

<div class="return-page-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <div class="small text-uppercase fw-bold text-success mb-1">Penjualan / Purna Jual</div>
        <h1 class="h3 fw-bold text-dark mb-1">Retur Barang</h1>
        <p class="text-muted mb-0">Catat barang yang dikembalikan tanpa membuka atau mengubah invoice terkunci.</p>
    </div>
    <a href="{{ route('penjualan.histori') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Histori Penjualan
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Periksa kembali data retur</div>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="return-kpi p-3">
            <div class="small text-muted mb-2"><i class="bi bi-arrow-return-left me-1"></i>Transaksi retur</div>
            <div class="kpi-value">{{ number_format($totalCases, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="return-kpi p-3">
            <div class="small text-muted mb-2"><i class="bi bi-box-seam me-1"></i>Unit dikembalikan</div>
            <div class="kpi-value">{{ number_format($totalQuantity, 0, ',', '.') }} <span class="fs-6 fw-normal">unit</span></div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="return-kpi p-3">
            <div class="small text-muted mb-2"><i class="bi bi-cash-coin me-1"></i>Total nilai retur</div>
            <div class="kpi-value">Rp {{ number_format($totalValue, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="row g-4 align-items-start mb-4">
    <div class="col-12 col-xl-8">
        <section class="card return-form-card">
            <div class="card-header bg-white border-0 px-3 px-md-4 pt-4 pb-0">
                <h2 class="h5 fw-bold mb-1">Catat retur baru</h2>
                <p class="small text-muted mb-0">Hanya item dengan sisa kuantitas pada invoice terkunci yang dapat dipilih.</p>
            </div>
            <div class="card-body p-3 p-md-4">
                @if(count($invoiceOptions) > 0)
                <form action="{{ route('penjualan.retur.store') }}" method="POST" id="formRetur"
                    data-items="{{ json_encode($invoiceOptions, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
                    data-selected-invoice="{{ old('invoice_id', $selectedInvoiceId ?? '') }}"
                    data-selected-rows="{{ json_encode(old('returns', []), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}"
                    data-selected-item="{{ $selectedItemIndex ?? '' }}"
                    data-selected-quantity="{{ $selectedQuantity ?? 1 }}"
                    data-selected-imeis="{{ json_encode($selectedImeis ?? [], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="invoice_id" class="form-label fw-semibold">Invoice terkunci <span class="text-danger">*</span></label>
                            <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required>
                                <option value="">Pilih invoice</option>
                                @foreach($invoiceOptions as $invoiceOption)
                                <option value="{{ $invoiceOption['id'] }}" @selected((string) old('invoice_id', $selectedInvoiceId ?? '' )===(string) $invoiceOption['id'])>
                                    {{ $invoiceOption['reference'] }} · {{ $invoiceOption['customer'] }}
                                </option>
                                @endforeach
                            </select>
                            @error('invoice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-2">
                                <div>
                                    <label class="form-label fw-semibold mb-0">Barang yang dikembalikan</label>
                                    <div class="form-text">Tambah beberapa barang; alasan dan kondisi diatur per baris.</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" id="addReturnRow" disabled>
                                    <i class="bi bi-plus-lg me-1"></i>Tambah barang
                                </button>
                            </div>
                            <div class="d-grid gap-3" id="returnRows"></div>
                        </div>

                        <div class="col-12">
                            <label for="tanggal_retur" class="form-label fw-semibold">Tanggal diterima <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_retur" id="tanggal_retur" class="form-control @error('tanggal_retur') is-invalid @enderror" value="{{ old('tanggal_retur', now()->toDateString()) }}" required>
                            @error('tanggal_retur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="return-summary p-3 rounded d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <div>
                                    <div class="small text-muted">Total perkiraan nilai pengembalian</div>
                                    <div class="h5 fw-bold text-success mb-0" id="nilaiRetur">Rp 0</div>
                                </div>
                                <div class="small text-muted align-self-sm-center" id="ringkasanItem">Belum ada barang dipilih.</div>
                            </div>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row justify-content-end gap-2 pt-1">
                            <a href="{{ route('penjualan.histori') }}" class="btn btn-light border">Batal</a>
                            <button type="submit" class="btn btn-success fw-semibold" id="submitRetur" disabled>
                                <i class="bi bi-check2-circle me-1"></i>Simpan semua retur
                            </button>
                        </div>
                    </div>
                </form>
                @else
                <div class="text-center py-4 px-2">
                    <div class="display-6 text-success mb-2"><i class="bi bi-clipboard-check"></i></div>
                    <h3 class="h6 fw-bold">Tidak ada item yang bisa diretur</h3>
                    <p class="small text-muted mb-0">Retur hanya dapat dibuat dari invoice terkunci yang masih memiliki kuantitas tersedia.</p>
                </div>
                @endif
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-4">
        <aside class="border rounded p-3 bg-white h-100">
            <h2 class="h6 fw-bold mb-3"><i class="bi bi-info-circle text-success me-1"></i>Ringkasan proses</h2>
            <ol class="small text-muted ps-3 mb-3">
                <li class="mb-2">Pilih satu invoice terkunci, lalu tambah semua barang yang dikembalikan.</li>
                <li class="mb-2">Pilih serial dari histori; alasan dan kondisi dapat berbeda pada tiap baris.</li>
                <li>Semua baris disimpan bersama, dengan nilai dihitung dari harga invoice.</li>
            </ol>
            <div class="small text-dark bg-light border rounded p-2">
                Invoice asli tidak diubah. Catatan kondisi retur juga tidak mengubah stok pembelian secara otomatis.
            </div>
        </aside>
    </div>
</div>

<section class="card return-ledger-card">
    <div class="card-header bg-white border-0 px-3 px-md-4 pt-4 pb-2 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
        <div>
            <h2 class="h5 fw-bold mb-1">Riwayat retur</h2>
            <p class="small text-muted mb-0">Rincian barang, nominal, serial, kondisi, dan pencatat.</p>
        </div>
        <span class="badge bg-light text-dark border">{{ $returns->total() }} transaksi</span>
    </div>
    <div class="card-body px-3 px-md-4 pt-2">
        @if($returns->count() > 0)
        <div class="table-responsive d-none d-lg-block">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal / Invoice</th>
                        <th>Barang / IMEI</th>
                        <th>Qty</th>
                        <th>Alasan / Kondisi</th>
                        <th>Catatan</th>
                        <th class="text-end">Nilai Retur / Modal</th>
                        <th>Pencatat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returns as $return)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $return->tanggal_retur->format('d/m/Y') }}</span>
                            <small class="d-block text-primary">{{ $return->invoice?->referensi ?? '-' }}</small>
                            <small class="text-muted">{{ $return->invoice?->nama_pelanggan ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $return->nama_barang }}</span>
                            @if($return->detail_imei)
                            <small class="d-block text-muted return-imei">{{ $return->detail_imei }}</small>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $return->kuantitas }} unit</td>
                        <td>
                            <span class="d-block">{{ $return->alasan }}</span>
                            <span class="badge {{ $return->kondisi === 'Rusak' ? 'bg-danger' : ($return->kondisi === 'Perlu pemeriksaan' ? 'bg-warning text-dark' : 'bg-success') }}">{{ $return->kondisi }}</span>
                        </td>
                        <td class="small text-muted">{{ $return->catatan ?: '-' }}</td>
                        <td class="text-end">
                            <span class="fw-bold d-block">Rp {{ number_format($return->nilai_retur, 0, ',', '.') }}</span>
                            <small class="text-muted d-block">Modal: Rp {{ number_format($return->nilai_modal, 0, ',', '.') }}</small>
                            <small class="text-danger d-block">Profit dikurangi: Rp {{ number_format($return->nilai_retur - $return->nilai_modal, 0, ',', '.') }}</small>
                        </td>
                        <td class="small">{{ $return->user?->name ?? 'Pengguna dihapus' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-lg-none d-grid gap-2">
            @foreach($returns as $return)
            <article class="return-mobile-entry p-3">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div>
                        <div class="fw-bold">{{ $return->nama_barang }}</div>
                        <div class="small text-primary">{{ $return->invoice?->referensi ?? '-' }} · {{ $return->invoice?->nama_pelanggan ?? '-' }}</div>
                    </div>
                    <span class="badge {{ $return->kondisi === 'Rusak' ? 'bg-danger' : ($return->kondisi === 'Perlu pemeriksaan' ? 'bg-warning text-dark' : 'bg-success') }}">{{ $return->kondisi }}</span>
                </div>
                <div class="row g-2 small mb-2">
                    <div class="col-6"><span class="text-muted">Tanggal</span>
                        <div>{{ $return->tanggal_retur->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-6"><span class="text-muted">Jumlah</span>
                        <div>{{ $return->kuantitas }} unit</div>
                    </div>
                    <div class="col-6"><span class="text-muted">Alasan</span>
                        <div>{{ $return->alasan }}</div>
                    </div>
                    <div class="col-6"><span class="text-muted">Nilai retur</span>
                        <div class="fw-bold">Rp {{ number_format($return->nilai_retur, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="small text-muted mb-2">Modal: Rp {{ number_format($return->nilai_modal, 0, ',', '.') }} · Profit dikurangi: <span class="text-danger">Rp {{ number_format($return->nilai_retur - $return->nilai_modal, 0, ',', '.') }}</span></div>
                @if($return->detail_imei)
                <div class="small mb-2"><span class="text-muted">IMEI / serial</span>
                    <div class="return-imei">{{ $return->detail_imei }}</div>
                </div>
                @endif
                @if($return->catatan)
                <div class="small mb-2"><span class="text-muted">Catatan</span>
                    <div>{{ $return->catatan }}</div>
                </div>
                @endif
                <div class="small text-muted border-top pt-2">Dicatat oleh {{ $return->user?->name ?? 'Pengguna dihapus' }}</div>
            </article>
            @endforeach
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox display-6 d-block mb-2"></i>
            Belum ada transaksi retur.
        </div>
        @endif
        <div class="mt-3">{{ $returns->links() }}</div>
    </div>
</section>

@if(count($invoiceOptions) > 0)
<script>
    const returnForm = document.getElementById('formRetur');
    const returnInvoices = JSON.parse(returnForm.dataset.items);
    const selectedInvoiceId = returnForm.dataset.selectedInvoice;
    const selectedItemIndex = returnForm.dataset.selectedItem;
    const selectedQuantity = Number(returnForm.dataset.selectedQuantity || 1);
    const selectedImeis = returnForm.dataset.selectedImeis;
    const invoiceSelect = document.getElementById('invoice_id');
    const returnRows = document.getElementById('returnRows');
    const addReturnRowButton = document.getElementById('addReturnRow');
    const submitButton = document.getElementById('submitRetur');
    const reasonOptions = ['Tidak sesuai pesanan', 'Berubah pikiran', 'Cacat atau rusak', 'Lainnya'];
    const conditionOptions = ['Layak jual', 'Perlu pemeriksaan', 'Rusak'];
    let nextRowId = 0;
    const formatRupiah = value => new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0
    }).format(value || 0);

    function selectedImeisForRow(row) {
        return Array.from(row.querySelector('.return-imeis').selectedOptions, option => option.value);
    }

    function createReturnRow(initial = {}) {
        const rowId = nextRowId++;
        const row = document.createElement('article');
        row.className = 'return-item-row border rounded p-3';
        row.dataset.rowId = rowId;
        row.dataset.initialImeis = JSON.stringify(Array.isArray(initial.imeis) ? initial.imeis : []);
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <h3 class="h6 fw-bold mb-0">Barang retur <span class="return-row-number"></span></h3>
                <button type="button" class="btn btn-sm btn-outline-danger remove-return-row" aria-label="Hapus baris retur"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold">Barang pada invoice <span class="text-danger">*</span></label>
                    <select class="form-select return-item-select" name="returns[${rowId}][item_index]" required disabled><option value="">Pilih invoice terlebih dahulu</option></select>
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-semibold">Qty retur <span class="text-danger">*</span></label>
                    <input class="form-control return-quantity" type="number" name="returns[${rowId}][kuantitas]" min="1" value="${Number(initial.kuantitas || 1)}" required disabled>
                    <div class="form-text return-remaining">Pilih barang terlebih dahulu.</div>
                </div>
                <div class="col-12 return-serial-wrap">
                    <label class="form-label fw-semibold">Pilih IMEI / serial <span class="text-danger">*</span></label>
                    <select class="form-select return-imeis" name="returns[${rowId}][imeis][]" multiple size="3" disabled></select>
                    <div class="form-text return-imei-help">Daftar serial diambil dari histori penjualan.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Alasan retur <span class="text-danger">*</span></label>
                    <select class="form-select return-reason" name="returns[${rowId}][alasan]" required><option value="">Pilih alasan</option></select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Kondisi saat diterima <span class="text-danger">*</span></label>
                    <select class="form-select return-condition" name="returns[${rowId}][kondisi]" required><option value="">Pilih kondisi</option></select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Catatan pemeriksaan</label>
                    <textarea class="form-control return-notes" name="returns[${rowId}][catatan]" rows="2" maxlength="2000" placeholder="Kelengkapan, kondisi fisik, atau tindak lanjut"></textarea>
                </div>
            </div>`;

        const itemSelect = row.querySelector('.return-item-select');
        const quantityInput = row.querySelector('.return-quantity');
        const imeiSelect = row.querySelector('.return-imeis');
        const reasonSelect = row.querySelector('.return-reason');
        const conditionSelect = row.querySelector('.return-condition');
        reasonOptions.forEach(reason => reasonSelect.add(new Option(reason, reason)));
        conditionOptions.forEach(condition => conditionSelect.add(new Option(condition, condition)));
        reasonSelect.value = initial.alasan || '';
        conditionSelect.value = initial.kondisi || '';
        row.querySelector('.return-notes').value = initial.catatan || '';
        row.querySelector('.remove-return-row').addEventListener('click', () => {
            row.remove();
            refreshRows();
        });
        itemSelect.addEventListener('change', () => {
            quantityInput.value = 1;
            row.dataset.initialImeis = '[]';
            imeiSelect.replaceChildren();
            refreshRows();
        });
        quantityInput.addEventListener('input', refreshRows);
        imeiSelect.addEventListener('change', refreshRows);
        reasonSelect.addEventListener('change', refreshRows);
        conditionSelect.addEventListener('change', refreshRows);
        returnRows.append(row);

        const invoice = returnInvoices.find(entry => String(entry.id) === invoiceSelect.value);
        itemSelect.disabled = !invoice;
        itemSelect.replaceChildren(new Option(invoice ? 'Pilih barang' : 'Pilih invoice terlebih dahulu', ''));
        if (invoice) {
            invoice.items.forEach(item => itemSelect.add(new Option(`${item.name} · sisa ${item.remaining} · Rp ${formatRupiah(item.price)}`, item.index)));
            if (initial.item_index !== undefined && invoice.items.some(item => String(item.index) === String(initial.item_index))) {
                itemSelect.value = String(initial.item_index);
                quantityInput.disabled = false;
                quantityInput.value = Number(initial.kuantitas || 1);
            }
        }

        return row;
    }

    function refreshRows() {
        const invoice = returnInvoices.find(entry => String(entry.id) === invoiceSelect.value);
        const rows = Array.from(returnRows.querySelectorAll('.return-item-row'));
        let allValid = Boolean(invoice && rows.length);
        let total = 0;
        let totalQuantity = 0;
        const quantityByItem = new Map();
        const imeisByItem = new Map();

        rows.forEach((row, index) => {
            row.querySelector('.return-row-number').textContent = index + 1;
            row.querySelector('.remove-return-row').disabled = rows.length === 1;
            const itemSelect = row.querySelector('.return-item-select');
            const quantityInput = row.querySelector('.return-quantity');
            const imeiSelect = row.querySelector('.return-imeis');
            const item = invoice?.items.find(entry => String(entry.index) === itemSelect.value);
            itemSelect.disabled = !invoice;
            quantityInput.disabled = !item;

            if (!item) {
                itemSelect.replaceChildren(new Option(invoice ? 'Pilih barang' : 'Pilih invoice terlebih dahulu', ''));
                if (invoice) invoice.items.forEach(entry => itemSelect.add(new Option(`${entry.name} · sisa ${entry.remaining} · Rp ${formatRupiah(entry.price)}`, entry.index)));
                row.querySelector('.return-remaining').textContent = 'Pilih barang terlebih dahulu.';
                row.querySelector('.return-serial-wrap').classList.add('d-none');
                allValid = false;
                return;
            }

            const itemKey = String(item.index);
            const otherQuantity = rows.reduce((sum, otherRow) => {
                if (otherRow === row || otherRow.querySelector('.return-item-select').value !== itemKey) return sum;
                return sum + Number(otherRow.querySelector('.return-quantity').value || 0);
            }, 0);
            const rowQuantity = Number(quantityInput.value || 0);
            const maxForRow = Math.max(0, item.remaining - otherQuantity);
            quantityInput.max = maxForRow;
            row.querySelector('.return-remaining').textContent = `${maxForRow} unit tersedia untuk baris ini · ${item.remaining} unit tersisa total`;
            quantityByItem.set(itemKey, (quantityByItem.get(itemKey) || 0) + rowQuantity);
            totalQuantity += rowQuantity;
            total += item.price * rowQuantity;

            const initialImeis = JSON.parse(row.dataset.initialImeis || '[]');
            const previouslySelected = [...new Set([...selectedImeisForRow(row), ...initialImeis])];
            const selectedElsewhere = new Set(rows
                .filter(otherRow => otherRow !== row && otherRow.querySelector('.return-item-select').value === itemKey)
                .flatMap(selectedImeisForRow));
            imeiSelect.replaceChildren();
            item.imeis.forEach(imei => {
                const option = new Option(imei, imei);
                option.disabled = selectedElsewhere.has(imei);
                option.selected = previouslySelected.includes(imei) && !option.disabled;
                imeiSelect.add(option);
            });
            row.dataset.initialImeis = '[]';

            const hasSerials = item.has_serials;
            row.querySelector('.return-serial-wrap').classList.toggle('d-none', !hasSerials);
            imeiSelect.disabled = !hasSerials;
            imeiSelect.required = hasSerials;
            row.querySelector('.return-imei-help').textContent = hasSerials ?
                `Pilih ${rowQuantity || 0} serial dari histori; pilihan tidak bisa dipakai pada baris lain.` :
                'Item ini tidak mencatat IMEI/serial pada invoice.';
            const selectedCount = selectedImeisForRow(row).length;
            const rowValid = rowQuantity > 0 &&
                rowQuantity <= maxForRow &&
                quantityByItem.get(itemKey) <= item.remaining &&
                (!hasSerials || selectedCount === rowQuantity) &&
                Boolean(row.querySelector('.return-reason').value) &&
                Boolean(row.querySelector('.return-condition').value);
            row.classList.toggle('border-danger', !rowValid);
            allValid = allValid && rowValid;
        });

        document.getElementById('nilaiRetur').textContent = `Rp ${formatRupiah(total)}`;
        document.getElementById('ringkasanItem').textContent = `${rows.length} baris · ${totalQuantity} unit dipilih`;
        submitButton.disabled = !allValid;
    }

    function resetRows(initialRows = [{}]) {
        returnRows.replaceChildren();
        initialRows.forEach(createReturnRow);
        refreshRows();
    }

    addReturnRowButton.addEventListener('click', () => {
        createReturnRow();
        refreshRows();
    });
    invoiceSelect.addEventListener('change', () => {
        addReturnRowButton.disabled = !invoiceSelect.value;
        resetRows([{}]);
    });

    invoiceSelect.value = selectedInvoiceId;
    addReturnRowButton.disabled = !invoiceSelect.value;
    const oldRows = JSON.parse(returnForm.dataset.selectedRows || '[]');
    if (oldRows.length) {
        resetRows(oldRows);
    } else if (selectedItemIndex !== '') {
        resetRows([{
            item_index: selectedItemIndex,
            kuantitas: selectedQuantity,
            imeis: JSON.parse(selectedImeis || '[]')
        }]);
    } else {
        resetRows([{}]);
    }
</script>
@endif
@endsection