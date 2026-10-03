<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedStatus = $request->input('status');

        $query = Pembelian::with('user');

        if (!empty($selectedStatus)) {
            $query->where('status', $selectedStatus);
        } else {
            // Sembunyikan data Jual dan Selesai hanya dari tampilan Semua.
            $query->whereNotIn('status', ['Jual', 'Selesai']);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_manual', 'like', "%{$search}%")
                    ->orWhere('kode_otomatis', 'like', "%{$search}%")
                    ->orWhere('nama_alamat', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('nama_toko', 'like', "%{$search}%")
                    ->orWhere('detail_imei', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedStatus === 'Selesai') {
            $pembelians = $query
                ->orderByRaw('CASE WHEN tanggal_terbit IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('tanggal_terbit')
                ->orderByDesc('id')
                ->get();
        } else {
            $pembelians = $query->latest()->get();
        }

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        return view('pembelian.index', compact('pembelians', 'barangs', 'tokos', 'devices', 'search', 'selectedStatus'));
    }

    public function lampiran(string $filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        abort_unless(in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'], true), 404);

        $path = 'lampiran_pembelian/' . $filename;
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return $disk->response($path, $filename, [
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function create()
    {
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        return view('pembelian.create', compact('barangs', 'tokos', 'devices'));
    }

    public function store(Request $request)
    {
        $titipanOptions = ['Tidak', 'Ya', 'Ya, (tidak ambil untung)'];
        $request->validate([
            'titipan' => ['sometimes', Rule::in($titipanOptions)],
            'items' => 'sometimes|array',
            'items.*.titipan' => ['nullable', Rule::in($titipanOptions)],
        ]);

        // 1. Dapatkan daftar item multi-input
        $itemsData = $request->input('items', []);

        // Fallback jika dikirim secara single-input standar
        if (empty($itemsData) && $request->has('nama_barang')) {
            $itemsData = [
                [
                    'kode_manual'  => $request->input('kode_manual'),
                    'nama_alamat'  => $request->input('nama_alamat'),
                    'nama_barang'  => $request->input('nama_barang'),
                    'nama_toko'    => $request->input('nama_toko'),
                    'via'          => $request->input('via'),
                    'nama_trader'  => $request->input('nama_trader'),
                    'tanggal_beli' => $request->input('tanggal_beli'),
                    'total_modal'  => $request->input('total_modal'),
                    'titipan'      => $request->input('titipan', 'Tidak'),
                    'status'       => $request->input('status', 'Belum Ready'),
                    'detail_imei'  => $request->input('detail_imei'),
                    'qty'          => $request->input('qty', 1),
                ]
            ];
        }

        if (empty($itemsData)) {
            return redirect()->back()->with('error', 'Tidak ada data transaksi yang dikirim.');
        }

        $masterPrices = Barang::whereIn('nama_barang', collect($itemsData)->pluck('nama_barang')->filter())
            ->pluck('harga_jual', 'nama_barang');

        $totalTercatat = 0;

        foreach ($itemsData as $idx => $item) {
            $titipan = $item['titipan'] ?? 'Tidak';
            if ($titipan === 'Ya, (tidak ambil untung)' && !$masterPrices->has($item['nama_barang'] ?? '')) {
                return redirect()->back()->withInput()->withErrors([
                    'nama_barang' => 'Barang tidak ditemukan di master barang, sehingga harga modal titipan tidak dapat diambil.',
                ]);
            }
            $totalModalBersih = $titipan === 'Ya, (tidak ambil untung)'
                ? round((float) $masterPrices->get($item['nama_barang']))
                : preg_replace('/\D/', '', (string) ($item['total_modal'] ?? 0));
            $via = $item['via'] ?? 'Tokopedia';
            $qty = ($via === 'COD' && !empty($item['qty'])) ? (int) $item['qty'] : 1;

            // Pengurusan file lampiran per baris item
            $files = [];
            if ($request->hasFile("items.{$idx}.file_lampiran")) {
                foreach ($request->file("items.{$idx}.file_lampiran") as $file) {
                    $files[] = $file->store('lampiran_pembelian', 'public');
                }
            } elseif ($request->hasFile('file_lampiran') && $idx === 0) {
                // Fallback untuk single-input upload
                foreach ($request->file('file_lampiran') as $file) {
                    $files[] = $file->store('lampiran_pembelian', 'public');
                }
            }

            for ($i = 0; $i < $qty; $i++) {
                Pembelian::create([
                    'user_id'       => Auth::id(),
                    'kode_manual'   => $item['kode_manual'] ?? null,
                    'nama_alamat'   => $item['nama_alamat'] ?? null,
                    'nama_barang'   => $item['nama_barang'] ?? null,
                    'nama_toko'     => $item['nama_toko'] ?? null,
                    'via'           => $via,
                    'nama_trader'   => $item['nama_trader'] ?? null,
                    'tanggal_beli'  => $item['tanggal_beli'] ?? date('Y-m-d'),
                    'total_modal'   => $totalModalBersih,
                    'titipan'       => $titipan,
                    'status'        => $item['status'] ?? 'Belum Ready',
                    'detail_imei'   => $item['detail_imei'] ?? null,
                    'kode_otomatis' => $this->generateUniqueKodeOtomatis(),
                    'file_lampiran' => !empty($files) ? $files : null,
                ]);
                $totalTercatat++;
            }
        }

        return redirect()->route('pembelian.index')->with('success', "Berhasil mencatat {$totalTercatat} data pembelian baru!");
    }

    private function generateUniqueKodeOtomatis(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $suffix = str_pad((string) random_int(1000, 999999), 6, '0', STR_PAD_LEFT);
            $kode = $prefix . $suffix;

            if (!Pembelian::where('kode_otomatis', $kode)->exists()) {
                return $kode;
            }
        }

        $fallbackKode = $prefix . now()->format('His') . '-' . random_int(1000, 9999);

        if (!Pembelian::where('kode_otomatis', $fallbackKode)->exists()) {
            return $fallbackKode;
        }

        throw new \RuntimeException('Gagal menghasilkan kode otomatis pembelian yang unik.');
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');

        $request->validate([
            'status' => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual,Selesai',
        ]);

        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat diubah.');
        }

        $updateData = ['status' => $status];

        // Jika status diubah dari Selesai, kosongkan referensi invoice
        if ($status !== 'Selesai') {
            $updateData['tanggal_terbit'] = null;
            $updateData['no_invoice']     = null;
        }

        $pembelian->update($updateData);

        return redirect()->back()->with('success', 'Status barang berhasil diperbarui!');
    }

    /**
     * Memperbarui status pembelian secara massal berdasarkan checkbox yang dicentang
     */
    public function updateStatusMassal(Request $request)
    {
        $pembelianIds = array_merge(
            $request->input('pembelian_ids', []),
            $request->input('pembelian_ids_mobile', [])
        );
        $pembelianIds = array_unique(array_filter($pembelianIds));

        if (empty($pembelianIds)) {
            return redirect()->back()->with('error', 'Pilih minimal satu item untuk diperbarui secara massal.');
        }

        $request->validate([
            'status_massal' => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual,Selesai',
        ]);

        $updatableIds = [];
        $lockedCount = 0;
        foreach (Pembelian::whereIn('id', $pembelianIds)->get() as $pembelian) {
            if (Invoice::isLockedForPurchase($pembelian)) {
                $lockedCount++;
            } else {
                $updatableIds[] = $pembelian->id;
            }
        }

        if (empty($updatableIds)) {
            return redirect()->back()->with('error', 'Semua data yang dipilih terkunci oleh invoice dan tidak dapat diubah.');
        }

        $statusBaru = $request->status_massal;
        $updateData = ['status' => $statusBaru];

        if ($statusBaru !== 'Selesai') {
            $updateData['tanggal_terbit'] = null;
            $updateData['no_invoice']     = null;
        }

        Pembelian::whereIn('id', $updatableIds)->update($updateData);

        $message = 'Status ' . count($updatableIds) . ' data pembelian berhasil diperbarui secara massal!';
        if ($lockedCount > 0) {
            $message .= ' ' . $lockedCount . ' data terkunci dilewati.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function updateMassal(Request $request)
    {
        $allowedFields = ['nama_trader', 'titipan', 'nama_toko', 'via', 'nama_alamat', 'tanggal_beli', 'total_modal'];
        $data = $request->validate([
            'pembelian_ids' => 'required|array|min:1',
            'pembelian_ids.*' => 'required|integer|distinct|exists:pembelians,id',
            'fields' => 'required|array|min:1',
            'fields.*' => ['required', Rule::in($allowedFields)],
            'nama_trader' => 'nullable|string|max:255',
            'titipan' => ['nullable', Rule::in(['Tidak', 'Ya', 'Ya, (tidak ambil untung)'])],
            'nama_toko' => 'nullable|string|max:255',
            'via' => 'nullable|string|max:255',
            'nama_alamat' => 'nullable|string|max:255',
            'tanggal_beli' => 'nullable|date',
            'total_modal' => 'nullable|numeric|min:0',
        ]);

        $fields = array_unique($data['fields']);
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                return redirect()->back()->with('error', 'Isi nilai untuk setiap field yang dipilih untuk diperbarui.');
            }
        }

        foreach (['nama_toko', 'via', 'tanggal_beli', 'total_modal'] as $requiredField) {
            if (in_array($requiredField, $fields, true) && blank($data[$requiredField])) {
                return redirect()->back()->with('error', 'Isi nilai untuk setiap field yang dipilih untuk diperbarui.');
            }
        }

        $requestedUpdates = array_intersect_key($data, array_flip($fields));
        $pembelians = Pembelian::whereIn('id', $data['pembelian_ids'])->get();
        $updatable = collect();
        $lockedCount = 0;
        foreach ($pembelians as $pembelian) {
            if (Invoice::isLockedForPurchase($pembelian)) {
                $lockedCount++;
            } else {
                $updatable->push($pembelian);
            }
        }

        if ($updatable->isEmpty()) {
            return redirect()->back()->with('error', 'Semua data yang dipilih terkunci oleh invoice dan tidak dapat diubah.');
        }

        $needsMasterPrice = in_array('total_modal', $fields, true)
            || (in_array('titipan', $fields, true) && $requestedUpdates['titipan'] === 'Ya, (tidak ambil untung)');
        $masterPrices = $needsMasterPrice
            ? Barang::whereIn('nama_barang', $updatable->pluck('nama_barang'))->pluck('harga_jual', 'nama_barang')
            : collect();

        if ($needsMasterPrice) {
            foreach ($updatable as $pembelian) {
                $effectiveTitipan = $requestedUpdates['titipan'] ?? $pembelian->titipan;
                if (
                    $effectiveTitipan === 'Ya, (tidak ambil untung)'
                    && !$masterPrices->has($pembelian->nama_barang)
                ) {
                    return redirect()->back()->withInput()->with('error', "Harga master tidak ditemukan untuk {$pembelian->nama_barang}; tidak ada data yang diubah.");
                }
            }
        }

        DB::transaction(function () use ($updatable, $requestedUpdates, $masterPrices, $needsMasterPrice, $fields) {
            $updatesByPurchase = [];

            foreach ($updatable as $pembelian) {
                $itemUpdates = $requestedUpdates;
                $effectiveTitipan = $itemUpdates['titipan'] ?? $pembelian->titipan;
                if ($needsMasterPrice && $effectiveTitipan === 'Ya, (tidak ambil untung)') {
                    $itemUpdates['total_modal'] = round((float) $masterPrices->get($pembelian->nama_barang));
                } elseif (in_array('total_modal', $fields, true)) {
                    $itemUpdates['total_modal'] = round((float) $itemUpdates['total_modal']);
                }

                $pembelian->update($itemUpdates);
                $updatesByPurchase[$pembelian->id] = $itemUpdates;
            }

            foreach (Invoice::whereNotNull('pembelian_data')->get() as $invoice) {
                $snapshotItems = $invoice->pembelian_data ?? [];
                $changed = false;

                foreach ($snapshotItems as &$snapshotItem) {
                    $purchaseId = (int) ($snapshotItem['pembelian_id'] ?? 0);
                    if (!isset($updatesByPurchase[$purchaseId])) {
                        continue;
                    }

                    foreach ($updatesByPurchase[$purchaseId] as $field => $value) {
                        $snapshotItem[$field] = $value;
                    }
                    if (array_key_exists('total_modal', $updatesByPurchase[$purchaseId])) {
                        $snapshotItem['total_profit'] = (float) ($snapshotItem['harga_jual'] ?? 0)
                            - (float) $updatesByPurchase[$purchaseId]['total_modal'];
                    }
                    $changed = true;
                }
                unset($snapshotItem);

                if ($changed) {
                    $invoice->pembelian_data = $snapshotItems;
                    $invoice->save();
                }
            }
        });

        $message = 'Data ' . $updatable->count() . ' barang berhasil diedit secara massal.';
        if ($lockedCount > 0) {
            $message .= " {$lockedCount} data terkunci dilewati.";
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat diubah.');
        }

        $titipanOptions = ['Tidak', 'Ya', 'Ya, (tidak ambil untung)'];
        $request->validate([
            'kode_manual'  => 'nullable|string|max:255',
            'nama_alamat'  => 'nullable|string|max:255',
            'nama_barang'  => 'required|string|max:255',
            'nama_toko'    => 'required|string|max:255',
            'via'          => 'required|string|max:255',
            'nama_trader'  => 'nullable|string|max:255',
            'tanggal_beli' => 'required|date',
            'total_modal'  => 'required',
            'titipan'      => ['required', Rule::in($titipanOptions)],
            'status'       => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual,Selesai',
            'detail_imei'  => 'nullable|string',
        ]);

        $data = $request->except(['file_lampiran', 'delete_files']);
        if ($data['titipan'] === 'Ya, (tidak ambil untung)') {
            $barang = Barang::where('nama_barang', $data['nama_barang'])->first();
            if (!$barang) {
                return redirect()->back()->withInput()->withErrors([
                    'nama_barang' => 'Barang tidak ditemukan di master barang, sehingga harga modal titipan tidak dapat diambil.',
                ]);
            }
            $data['total_modal'] = round((float) $barang->harga_jual);
        } else {
            $data['total_modal'] = preg_replace('/\D/', '', (string) $data['total_modal']);
        }
        $data['detail_imei'] = $request->input('detail_imei');

        $currentFiles = $pembelian->file_lampiran ?? [];

        if ($request->has('delete_files')) {
            foreach ($request->delete_files as $delIdx) {
                if (isset($currentFiles[$delIdx])) {
                    Storage::disk('public')->delete($currentFiles[$delIdx]);
                    unset($currentFiles[$delIdx]);
                }
            }
            $currentFiles = array_values($currentFiles);
        }

        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $currentFiles[] = $file->store('lampiran_pembelian', 'public');
            }
        }

        $data['file_lampiran'] = $currentFiles;
        $pembelian->update($data);

        return redirect()->route('pembelian.index')->with('success', 'Data Pembelian berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat dihapus.');
        }

        if (!empty($pembelian->file_lampiran)) {
            foreach ($pembelian->file_lampiran as $filePath) {
                Storage::disk('public')->delete($filePath);
            }
        }

        $pembelian->delete();

        return redirect()->route('pembelian.index')->with('success', 'Data Pembelian berhasil dihapus!');
    }

    public function historiRekap(Request $request)
    {
        $search = $request->input('search');

        $pembelians = Pembelian::whereIn('status', ['Selesai', 'Retur'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_manual', 'like', "%{$search}%")
                        ->orWhere('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('nama_alamat', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('detail_imei', 'like', "%{$search}%")
                        ->orWhere('no_invoice', 'like', "%{$search}%");
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // Ambil semua invoice untuk pencocokan data harga jual terbaru
        $invoices = Invoice::all();

        foreach ($pembelians as $item) {
            $item->invoice_locked = false;

            foreach ($invoices as $inv) {
                $found = $item->no_invoice === $inv->referensi;

                if (!empty($inv->pembelian_data) && is_array($inv->pembelian_data)) {
                    foreach ($inv->pembelian_data as $snap) {
                        if (
                            (isset($snap['pembelian_id']) && $snap['pembelian_id'] == $item->id) ||
                            (isset($snap['detail_imei']) && !empty($item->detail_imei) && $snap['detail_imei'] == $item->detail_imei)
                        ) {
                            $found = true;
                            $item->no_invoice      = $inv->referensi;
                            $item->tanggal_terbit  = $inv->tanggal;
                            $item->invoice_locked = (bool) $inv->is_locked;
                            if (isset($snap['harga_jual']) && $snap['harga_jual'] > 0) {
                                $item->harga_jual = (float) $snap['harga_jual'];
                            }
                            break;
                        }
                    }
                }

                if ($found) {
                    $item->invoice_locked = (bool) $inv->is_locked;
                    break;
                }
            }

            // Hitung Ulang Total Profit Secara Otomatis: (Harga Jual - Total Modal Item Ini)
            $modal     = (float) ($item->total_modal ?? 0);
            $hargaJual = (float) ($item->harga_jual ?? 0);

            $item->total_profit = $hargaJual - $modal;
        }

        return view('pembelian.histori_rekap', compact('pembelians', 'search'));
    }

    public function updateHistoriRekap(Request $request, $id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat diubah.');
        }

        $data = $request->validate([
            'kode_manual'  => 'nullable|string|max:255',
            'nama_alamat'  => 'nullable|string|max:255',
            'nama_barang'  => 'required|string|max:255',
            'nama_device'  => 'nullable|string|max:255',
            'nama_toko'    => 'required|string|max:255',
            'via'          => 'required|string|max:255',
            'nama_trader'  => 'nullable|string|max:255',
            'titipan'      => ['required', Rule::in(['Tidak', 'Ya', 'Ya, (tidak ambil untung)'])],
            'detail_imei'  => 'nullable|string|max:250',
            'total_modal'  => 'required|numeric|min:0',
        ]);

        $previousImei = $pembelian->detail_imei;
        $previousName = $pembelian->nama_barang;
        $invoice = $pembelian->no_invoice
            ? Invoice::where('referensi', $pembelian->no_invoice)->first()
            : null;

        if (!$invoice) {
            $invoice = Invoice::all()->first(function ($candidate) use ($pembelian, $previousImei, $previousName) {
                foreach ($candidate->pembelian_data ?? [] as $snapshotItem) {
                    if (isset($snapshotItem['pembelian_id']) && (int) $snapshotItem['pembelian_id'] === (int) $pembelian->id) {
                        return true;
                    }

                    if (
                        !isset($snapshotItem['pembelian_id'])
                        && !empty($previousImei)
                        && ($snapshotItem['detail_imei'] ?? null) === $previousImei
                        && ($snapshotItem['nama_barang'] ?? null) === $previousName
                    ) {
                        return true;
                    }
                }

                return false;
            });
        }

        if ($invoice?->is_locked) {
            return redirect()->back()->with('error', 'Invoice ini terkunci dan datanya tidak dapat diubah.');
        }

        $updated = DB::transaction(function () use ($pembelian, $invoice, $data, $previousImei, $previousName) {
            if (!$invoice) {
                $pembelian->update($data);
                return true;
            }

            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->first();
            if (!$invoice || $invoice->is_locked) {
                return false;
            }

            $pembelian->update($data);

            $snapshotItems = $invoice->pembelian_data ?? [];
            $snapshotChanged = false;

            foreach ($snapshotItems as &$snapshotItem) {
                $matchesPurchase = isset($snapshotItem['pembelian_id'])
                    && (int) $snapshotItem['pembelian_id'] === (int) $pembelian->id;
                $matchesLegacyItem = !isset($snapshotItem['pembelian_id'])
                    && !empty($previousImei)
                    && ($snapshotItem['detail_imei'] ?? null) === $previousImei
                    && ($snapshotItem['nama_barang'] ?? null) === $previousName;

                if (!$matchesPurchase && !$matchesLegacyItem) {
                    continue;
                }

                foreach (['nama_barang', 'nama_device', 'nama_toko', 'via', 'nama_trader', 'titipan', 'detail_imei', 'total_modal'] as $field) {
                    $snapshotItem[$field] = $data[$field] ?? null;
                }
                $snapshotItem['nama_alamat'] = $data['nama_alamat'] ?? null;
                $snapshotItem['total_profit'] = (float) ($snapshotItem['harga_jual'] ?? 0) - (float) $data['total_modal'];
                $snapshotChanged = true;
            }
            unset($snapshotItem);

            if ($snapshotChanged) {
                $invoice->pembelian_data = $snapshotItems;
            }

            $invoiceItems = $invoice->items ?? [];
            $itemsChanged = false;
            foreach ($invoiceItems as &$invoiceItem) {
                $purchaseIds = array_map('intval', $invoiceItem['pembelian_ids'] ?? []);
                if (count($purchaseIds) !== 1 || $purchaseIds[0] !== (int) $pembelian->id) {
                    continue;
                }

                foreach (['nama_barang', 'nama_device', 'nama_toko', 'via', 'nama_trader', 'titipan'] as $field) {
                    $invoiceItem[$field] = $data[$field] ?? null;
                }
                $invoiceItem['detail_imei'] = $data['detail_imei'] ?? null;
                $invoiceItem['imei_list'] = preg_split('/[\r\n,]+/', trim($data['detail_imei'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                $invoiceItem['total_modal'] = (float) $data['total_modal'];
                $invoiceItem['total_profit'] = (float) ($invoiceItem['harga'] ?? 0) - (float) $data['total_modal'];
                $itemsChanged = true;
            }
            unset($invoiceItem);

            if ($itemsChanged) {
                $invoice->items = $invoiceItems;
            }

            if ($snapshotChanged || $itemsChanged) {
                $invoice->save();
            }

            return true;
        });

        if (!$updated) {
            return redirect()->back()->with('error', 'Invoice ini terkunci dan datanya tidak dapat diubah.');
        }

        return redirect()->back()->with('success', 'Data histori rekap dan rincian invoice berhasil diperbarui.');
    }

    /**
     * Mengembalikan status transaksi dari Selesai ke status rekap aktif (Rollback)
     */
    public function restoreStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual,Selesai',
        ]);

        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat dibatalkan status Selesainya.');
        }

        $updateData = ['status' => $request->status];

        if ($request->status !== 'Selesai') {
            $updateData['tanggal_terbit'] = null;
            $updateData['no_invoice']     = null;
        }

        $pembelian->update($updateData);

        return redirect()->back()->with('success', 'Status transaksi berhasil dikembalikan! Data kini aktif kembali.');
    }

    public function barangSiapJual(Request $request)
    {
        $search = $request->input('search');

        $pembelians = Pembelian::where('status', 'Jual')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $barangs = Barang::all()->keyBy('nama_barang');

        return view('pembelian.siap_jual', compact('pembelians', 'search', 'barangs'));
    }

    public function toggleCheck($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return response()->json([
                'success' => false,
                'message' => 'Data ini terkunci oleh invoice dan tidak dapat diubah.',
            ], 423);
        }

        $pembelian->is_checked = !$pembelian->is_checked;
        $pembelian->save();

        return response()->json([
            'success'    => true,
            'is_checked' => $pembelian->is_checked
        ]);
    }

    public function createInvoice(Request $request)
    {
        $ids = $request->input('pembelian_ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu barang yang ingin dijual.');
        }

        $pembelians = Pembelian::whereIn('id', $ids)->get();

        foreach ($pembelians as $pembelian) {
            if (Invoice::isLockedForPurchase($pembelian)) {
                return redirect()->back()->with('error', 'Data yang dipilih mencakup transaksi yang terkunci oleh invoice.');
            }
        }

        $barangs    = Barang::all()->keyBy('nama_barang');

        $groupedItems = [];
        foreach ($pembelians as $item) {
            $namaBarang   = $item->nama_barang;
            $masterBarang = $barangs[$namaBarang] ?? null;
            $hargaJual    = $masterBarang ? $masterBarang->harga_jual : 0;

            $groupKey = Str::slug($namaBarang) . '_' . $hargaJual;

            $imeis = [];
            if (!empty($item->detail_imei)) {
                $cleanedImei = str_replace(["\r", ","], "\n", $item->detail_imei);
                $lines = explode("\n", $cleanedImei);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (!empty($trimmed)) {
                        $imeis[] = $trimmed;
                    }
                }
            }

            if (empty($imeis)) {
                $imeis = ['-'];
            }

            if (!isset($groupedItems[$groupKey])) {
                $groupedItems[$groupKey] = [
                    'pembelian_ids' => [$item->id],
                    'nama_barang'   => $namaBarang,
                    'imei_list'     => $imeis,
                    'kuantitas'     => count($imeis),
                    'harga'         => $hargaJual,
                ];
                $groupedItems[$groupKey]['jumlah'] = $groupedItems[$groupKey]['kuantitas'] * $hargaJual;
            } else {
                $groupedItems[$groupKey]['pembelian_ids'][] = $item->id;
                $groupedItems[$groupKey]['imei_list']     = array_merge($groupedItems[$groupKey]['imei_list'], $imeis);
                $groupedItems[$groupKey]['kuantitas']     = count($groupedItems[$groupKey]['imei_list']);
                $groupedItems[$groupKey]['jumlah']        = $groupedItems[$groupKey]['kuantitas'] * $hargaJual;
            }
        }

        $lastInvoice = Invoice::latest()->first();
        $nextNumber  = $lastInvoice ? ((int)substr($lastInvoice->referensi, -5)) + 1 : 1;
        $referensi   = 'INV/' . sprintf('%05d', $nextNumber);

        return view('pembelian.buat_invoice', compact('groupedItems', 'referensi'));
    }

    public function saveChecklist(Request $request)
    {
        Pembelian::where('user_id', Auth::id())->update(['is_checked' => false]);

        if ($request->has('checked_ids')) {
            Pembelian::whereIn('id', $request->checked_ids)->update(['is_checked' => true]);
        }

        return redirect()->back()->with('success', 'Status checklist ringkasan pesanan berhasil disimpan!');
    }

    public function storeInvoice(Request $request)
    {
        $request->validate([
            'referensi'      => 'required|string|unique:invoices,referensi',
            'tanggal'        => 'required|date',
            'jatuh_tempo'    => 'required|date',
            'nama_pelanggan' => 'required|string|max:255',
            'items'          => 'required|array',
        ]);

        $subtotal = 0;
        foreach ($request->items as $item) {
            $subtotal += $item['jumlah'];
        }

        $penyebut = function ($nilai) use (&$penyebut) {
            $nilai = abs((int)$nilai);
            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " " . $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = $penyebut($nilai - 10) . " Belas";
            } else if ($nilai < 100) {
                $temp = $penyebut((int)($nilai / 10)) . " Puluh" . $penyebut($nilai % 10);
            } else if ($nilai < 200) {
                $temp = $penyebut((int)($nilai / 100)) . " Ratus" . $penyebut($nilai % 100);
            } else if ($nilai < 1000) {
                $temp = $penyebut((int)($nilai / 100)) . " Ratus" . $penyebut($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = $penyebut((int)($nilai / 1000)) . " Seribu" . $penyebut($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = $penyebut((int)($nilai / 1000)) . " Ribu" . $penyebut($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = $penyebut((int)($nilai / 1000000)) . " Juta" . $penyebut($nilai % 1000000);
            } else if ($nilai < 1000000000000) {
                $temp = $penyebut((int)($nilai / 1000000000)) . " Milyar" . $penyebut(fmod($nilai, 1000000000));
            }
            return $temp;
        };

        $terbilangStr = trim($penyebut($subtotal)) . " Rupiah";

        $allPembelianIds = [];
        foreach ($request->items as $item) {
            if (isset($item['pembelian_ids']) && is_array($item['pembelian_ids'])) {
                foreach ($item['pembelian_ids'] as $pId) {
                    $allPembelianIds[] = $pId;
                }
            }
        }
        $allPembelianIds = array_unique($allPembelianIds);

        $pembelianItems = Pembelian::whereIn('id', $allPembelianIds)->get();
        foreach ($pembelianItems as $pembelian) {
            if (Invoice::isLockedForPurchase($pembelian)) {
                return redirect()->back()->with('error', 'Data yang dipilih mencakup transaksi yang terkunci oleh invoice.');
            }
        }

        $snapshotItems = [];
        foreach ($request->items as $invItem) {
            $pIds = $invItem['pembelian_ids'] ?? [];
            $matchedPembelians = $pembelianItems->whereIn('id', $pIds);

            $hargaJualSatuan = isset($invItem['harga']) ? (float) $invItem['harga'] : 0;

            foreach ($matchedPembelians as $pItem) {
                $modalItem = (float) ($pItem->total_modal ?? 0);

                $snapshotItems[] = [
                    'pembelian_id'  => $pItem->id,
                    'nama_barang'   => $pItem->nama_barang,
                    'nama_device'   => $pItem->nama_device ?? null,
                    'nama_alamat'   => $pItem->nama_alamat ?? null,
                    'nama_toko'     => $pItem->nama_toko,
                    'via'           => $pItem->via,
                    'titipan'       => $pItem->titipan ?? 'Tidak',
                    'nama_trader'   => $pItem->nama_trader,
                    'detail_imei'   => $pItem->detail_imei ?? '-',
                    'tanggal_beli'  => $pItem->tanggal_beli,
                    'total_modal'   => $modalItem,
                    'harga_jual'    => $hargaJualSatuan,
                    'total_profit'  => $hargaJualSatuan - $modalItem,
                    'file_lampiran' => $pItem->file_lampiran ?? [],
                ];
            }
        }

        $invoice = Invoice::create([
            'referensi'        => $request->referensi,
            'tanggal'          => $request->tanggal,
            'jatuh_tempo'      => $request->jatuh_tempo,
            'nama_pelanggan'   => $request->nama_pelanggan,
            'alamat_pelanggan' => $request->alamat_pelanggan ?? 'DKI Jakarta',
            'items'            => $request->items,
            'pembelian_data'   => $snapshotItems,
            'subtotal'         => $subtotal,
            'total'            => $subtotal,
            'terbilang'        => $terbilangStr,
            'status_payment'   => 'belum',
        ]);

        if (!empty($allPembelianIds)) {
            Pembelian::whereIn('id', $allPembelianIds)->update([
                'status'         => 'Selesai',
                'tanggal_terbit' => $request->tanggal,
                'no_invoice'     => $request->referensi,
            ]);
        }

        return redirect()->route('invoice.show', $invoice->id)->with('success', 'Invoice berhasil diterbitkan!');
    }

    public function showInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        return view('pembelian.print_invoice', compact('invoice'));
    }

    /**
     * Menghapus Invoice dan menandai item pembelian terkait sebagai bermasalah.
     */
    public function destroyInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->is_locked) {
            return redirect()->back()->with('error', 'Invoice ini terkunci dan tidak dapat dihapus.');
        }

        // Ambil data snapshot pembelian yang terikat pada invoice ini
        $rawPembelianData = $invoice->pembelian_data;
        if (is_string($rawPembelianData)) {
            $rawPembelianData = json_decode($rawPembelianData, true);
        }

        if (!empty($rawPembelianData) && is_array($rawPembelianData)) {
            $pembelianIds = [];
            foreach ($rawPembelianData as $snap) {
                if (isset($snap['pembelian_id'])) {
                    $pembelianIds[] = $snap['pembelian_id'];
                }
            }

            if (!empty($pembelianIds)) {
                // Tandai item sebagai bermasalah dan hapus referensi invoice.
                Pembelian::whereIn('id', array_unique($pembelianIds))->update([
                    'status'         => 'Bermasalah',
                    'no_invoice'     => null,
                    'tanggal_terbit' => null,
                ]);
            }
        } else {
            // Fallback jika tidak ada ID snapshot, hapus berdasarkan no_invoice
            Pembelian::where('no_invoice', $invoice->referensi)->update([
                'status'         => 'Bermasalah',
                'no_invoice'     => null,
                'tanggal_terbit' => null,
            ]);
        }

        // Hapus record invoice
        $invoice->delete();

        return redirect()->back()->with('success', 'Data Invoice berhasil dihapus dan status barang diubah menjadi Bermasalah!');
    }
}
