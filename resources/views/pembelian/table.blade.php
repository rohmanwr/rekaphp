<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Kode Sistem</th>
                <th>Kode Manual</th>
                <th>Barang & Toko</th>
                <th>Via</th>
                <th>Tgl Beli</th>
                <th>Total Modal</th>
                <th>Deskripsi / IMEI</th>
                <th>Status Ready</th>
                <th>Lampiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembelians as $item)
            <tr>
                <td><span class="badge bg-dark">{{ $item->kode_otomatis }}</span></td>
                <td>{{ $item->kode_manual ?? '-' }}</td>
                <td>
                    <strong>{{ $item->nama_barang }}</strong><br>
                    <small class="text-muted"><i class="bi bi-shop"></i> {{ $item->nama_toko }}</small>
                </td>
                <td>
                    @php
                    $badgeColor = match($item->via) {
                    'Tokopedia' => 'bg-success',
                    'Shopee' => 'bg-warning text-dark',
                    'Lazada' => 'bg-primary',
                    'TikTok' => 'bg-dark',
                    default => 'bg-secondary'
                    };
                    @endphp
                    <span class="badge {{ $badgeColor }}">{{ $item->via }}</span>
                </td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_beli)->format('d/m/Y') }}</td>
                <td class="fw-bold">Rp {{ number_format($item->total_modal, 0, ',', '.') }}</td>
                <td><small class="text-break">{{ $item->deskripsi_imei ?? '-' }}</small></td>
                <td>
                    <form action="{{ route('pembelian.toggleReady', $item->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @if($item->is_ready)
                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="bi bi-check-circle-fill"></i> Ready
                        </button>
                        @else
                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                            <i class="bi bi-clock-history"></i> Belum Ready
                        </button>
                        @endif
                    </form>
                </td>
                <td>
                    @if($item->file_lampiran)
                    <a href="{{ asset('storage/' . $item->file_lampiran) }}" target="_blank" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-paperclip"></i> Lihat File
                    </a>
                    @else
                    <span class="text-muted small">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data rekap pembelian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($pembelians->hasPages())
<div class="card-footer bg-white border-0 pt-3">
    {{ $pembelians->appends(['search' => $search])->links() }}
</div>
@endif