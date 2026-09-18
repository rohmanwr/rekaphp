@extends('layouts.app')

@section('title', 'Buat Invoice Penjualan')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Form Pembuatan Invoice</h3>
            <p class="text-muted small mb-0">Lengkapi data pelanggan di bawah ini sebelum menerbitkan invoice.</p>
        </div>
        <a href="{{ route('pembelian.siap_jual') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
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

    <form action="{{ route('invoice.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Kolom Informasi Pelanggan & Tanggal -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3">Detail Pelanggan</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Referensi</label>
                        <input type="text" name="referensi" class="form-control" value="{{ $referensi }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Pelanggan</label>
                        <input type="text" name="nama_pelanggan" class="form-control" placeholder="Contoh: Agus" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat / Kota</label>
                        <textarea name="alamat_pelanggan" class="form-control" rows="2" placeholder="Contoh: DKI Jakarta">DKI Jakarta</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Invoice</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mt-2">
                        <i class="bi bi-check-circle-fill me-1"></i> Terbitkan Invoice
                    </button>
                </div>
            </div>

            <!-- Kolom Ringkasan Barang yang Digrouping -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3">Ringkasan Barang Dipilih</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Barang</th>
                                    <th>Detail IMEI (Max 5 Kolom)</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach($groupedItems as $index => $item)
                                @php $grandTotal += $item['jumlah']; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $item['nama_barang'] }}</strong>
                                        @foreach($item['pembelian_ids'] as $pId)
                                        <input type="hidden" name="items[{{ $index }}][pembelian_ids][]" value="{{ $pId }}">
                                        @endforeach
                                        <input type="hidden" name="items[{{ $index }}][nama_barang]" value="{{ $item['nama_barang'] }}">
                                    </td>
                                    <td>
                                        <!-- Tampilan IMEI Maksimal 5 Kolom ke Samping & Baris Tak Terbatas -->
                                        @if(!empty($item['imei_list']) && count($item['imei_list']) > 0)
                                        <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 4px 10px; font-family: monospace; font-size: 0.8rem;" class="text-muted">
                                            @foreach($item['imei_list'] as $imei)
                                            <div>{{ $imei }}</div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="items[{{ $index }}][deskripsi_imei]" value="{{ implode("\n", $item['imei_list']) }}">
                                        @else
                                        <span class="text-muted">-</span>
                                        <input type="hidden" name="items[{{ $index }}][deskripsi_imei]" value="-">
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ $item['kuantitas'] }}
                                        <input type="hidden" name="items[{{ $index }}][kuantitas]" value="{{ $item['kuantitas'] }}">
                                    </td>
                                    <td class="text-end">
                                        Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                        <input type="hidden" name="items[{{ $index }}][harga]" value="{{ $item['harga'] }}">
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($item['jumlah'], 0, ',', '.') }}
                                        <input type="hidden" name="items[{{ $index }}][jumlah]" value="{{ $item['jumlah'] }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-top">
                                    <td colspan="4" class="text-end fw-bold fs-6">Total Tagihan:</td>
                                    <td class="text-end fw-bold fs-6 text-success">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection