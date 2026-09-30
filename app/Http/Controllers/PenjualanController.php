<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Toko;
use App\Models\Device;
use App\Models\Invoice;
use App\Models\ReturBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    /**
     * Menampilkan menu Barang Siap Jual (Status: "Jual")
     */
    public function siapJual(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get()->keyBy('nama_barang');
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $siapJualBarangs = Pembelian::where('status', 'Jual')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_device', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('detail_imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $pembelians = $siapJualBarangs;

        return view('penjualan.siap_jual', compact('pembelians', 'search', 'barangs', 'tokos', 'devices'));
    }

    /**
     * Menampilkan menu Histori Penjualan
     */
    public function historiPenjualan(Request $request)
    {
        $search = $request->input('search');

        $invoices = Invoice::withSum('returns as retur_total_nilai', 'nilai_retur')
            ->withSum('returns as retur_total_modal', 'nilai_modal')
            ->withSum('returns as retur_total_kuantitas', 'kuantitas')
            ->with('returns:id,invoice_id,item_index,kuantitas,detail_imei')
            ->when($search, function ($query, $search) {
                return $query->where('referensi', 'like', "%{$search}%")
                    ->orWhere('nama_pelanggan', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        foreach ($invoices as $invoice) {
            $sourceItems = !empty($invoice->pembelian_data) && is_array($invoice->pembelian_data)
                ? $invoice->pembelian_data
                : (!empty($invoice->items) && is_array($invoice->items) ? $invoice->items : []);

            $normalizedItems = [];
            $dataHasChanged = false;

            foreach ($sourceItems as $it) {
                $namaBarang = $it['nama_barang'] ?? ($it['deskripsi'] ?? 'Barang Invoice');

                $imeiVal = '-';
                if (isset($it['detail_imei']) && !empty($it['detail_imei'])) {
                    $imeiVal = $it['detail_imei'];
                } elseif (isset($it['imei']) && !empty($it['imei'])) {
                    $imeiVal = $it['imei'];
                } elseif (isset($it['deskripsi_imei']) && !empty($it['deskripsi_imei'])) {
                    $imeiVal = $it['deskripsi_imei'];
                } elseif (isset($it['imei_list']) && is_array($it['imei_list'])) {
                    $imeiVal = implode("\n", $it['imei_list']);
                }

                $tokoVal = $it['nama_toko'] ?? ($it['toko'] ?? '-');
                $viaVal = $it['via'] ?? ($it['platform'] ?? '-');
                $tglBeliVal = $it['tanggal_beli'] ?? $invoice->tanggal;
                $lampiranVal = $it['file_lampiran'] ?? [];

                // Cari data asli ke tabel Pembelian untuk Total Modal
                $pembelianRecord = null;
                if (isset($it['pembelian_id'])) {
                    $pembelianRecord = Pembelian::find($it['pembelian_id']);
                } elseif (isset($it['pembelian_ids']) && is_array($it['pembelian_ids']) && count($it['pembelian_ids']) > 0) {
                    $pembelianRecord = Pembelian::whereIn('id', $it['pembelian_ids'])->first();
                } else {
                    $pembelianRecord = Pembelian::where('nama_barang', 'like', "%{$namaBarang}%")
                        ->orderBy('tanggal_beli', 'desc')
                        ->first();
                }

                $modalVal = 0;
                if ($pembelianRecord && !empty($pembelianRecord->total_modal)) {
                    $modalVal = $pembelianRecord->total_modal;
                } else {
                    $modalVal = $it['total_modal'] ?? 0;
                }

                if ($pembelianRecord) {
                    if ($imeiVal === '-' || empty($imeiVal)) $imeiVal = $pembelianRecord->detail_imei ?? '-';
                    if ($tokoVal === '-' || empty($tokoVal)) $tokoVal = $pembelianRecord->nama_toko ?? '-';
                    if ($viaVal === '-' || empty($viaVal)) $viaVal = $pembelianRecord->via ?? '-';
                    if (empty($lampiranVal)) $lampiranVal = $pembelianRecord->file_lampiran ?? [];
                    if (empty($tglBeliVal)) $tglBeliVal = $pembelianRecord->tanggal_beli;
                }

                $hargaJualVal = 0;
                if (isset($it['harga_jual']) && is_numeric($it['harga_jual'])) {
                    $hargaJualVal = (float) $it['harga_jual'];
                } elseif (isset($it['harga']) && is_numeric($it['harga'])) {
                    $hargaJualVal = (float) $it['harga'];
                } elseif (isset($it['jumlah']) && isset($it['kuantitas']) && $it['kuantitas'] > 0) {
                    $hargaJualVal = (float) ($it['jumlah'] / $it['kuantitas']);
                } elseif (isset($it['jumlah']) && is_numeric($it['jumlah'])) {
                    $hargaJualVal = (float) $it['jumlah'];
                } else {
                    $hargaJualVal = 0;
                }

                $profitVal = $hargaJualVal - $modalVal;

                if (!isset($it['harga_jual']) || $it['harga_jual'] != $hargaJualVal) {
                    $dataHasChanged = true;
                }

                $normalizedItems[] = [
                    'pembelian_id'  => $it['pembelian_id'] ?? null,
                    'nama_barang'   => $namaBarang,
                    'nama_device'   => $it['nama_device'] ?? ($pembelianRecord->nama_device ?? null),
                    'nama_alamat'   => $it['nama_alamat'] ?? ($pembelianRecord->nama_alamat ?? null),
                    'nama_toko'     => $tokoVal,
                    'via'           => $viaVal,
                    'detail_imei'   => $imeiVal,
                    'tanggal_beli'  => $tglBeliVal,
                    'total_modal'   => $modalVal,
                    'harga_jual'    => $hargaJualVal,
                    'total_profit'  => $profitVal,
                    'file_lampiran' => $lampiranVal,
                ];
            }

            // Gunakan Query Builder langsung agar tidak menimpa status_payment di DB
            if (!$invoice->is_locked && $dataHasChanged && !empty($normalizedItems)) {
                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update(['pembelian_data' => json_encode($normalizedItems)]);
                $invoice->pembelian_data = $normalizedItems;
            }
        }

        return view('penjualan.histori', compact('invoices', 'search'));
    }

    public function returIndex(Request $request)
    {
        $invoices = Invoice::where('is_locked', true)->latest('tanggal')->get();
        $returnRecords = ReturBarang::whereIn('invoice_id', $invoices->pluck('id'))
            ->get(['invoice_id', 'item_index', 'kuantitas', 'detail_imei']);
        $usage = [];
        $returnedImeisByItem = [];
        foreach ($returnRecords as $returnRecord) {
            $key = $returnRecord->invoice_id . ':' . $returnRecord->item_index;
            $usage[$key] = ($usage[$key] ?? 0) + (int) $returnRecord->kuantitas;
            $returnedImeisByItem[$key] = array_merge(
                $returnedImeisByItem[$key] ?? [],
                array_map('strtolower', $this->parseImeis($returnRecord->detail_imei ?? ''))
            );
        }

        $invoiceOptions = [];
        foreach ($invoices as $invoice) {
            $items = is_array($invoice->items) ? array_values($invoice->items) : [];
            $availableItems = [];

            foreach ($items as $index => $item) {
                if (!is_array($item)) {
                    continue;
                }

                $details = $this->getInvoiceItemDetails($item);
                $itemKey = $invoice->id . ':' . $index;
                $returned = (int) ($usage[$itemKey] ?? 0);
                $remaining = max(0, $details['quantity'] - $returned);
                $returnedImeis = $returnedImeisByItem[$itemKey] ?? [];
                $availableImeis = array_values(array_filter(
                    $details['imeis'],
                    fn($imei) => !in_array(strtolower($imei), $returnedImeis, true)
                ));

                if ($remaining > 0) {
                    $availableItems[] = [
                        'index' => $index,
                        'name' => $details['name'],
                        'quantity' => $details['quantity'],
                        'remaining' => $remaining,
                        'price' => $details['price'],
                        'imeis' => $availableImeis,
                        'has_serials' => count($details['imeis']) > 0,
                    ];
                }
            }

            if ($availableItems) {
                $invoiceOptions[] = [
                    'id' => $invoice->id,
                    'reference' => $invoice->referensi,
                    'customer' => $invoice->nama_pelanggan,
                    'items' => $availableItems,
                ];
            }
        }

        $returns = ReturBarang::with(['invoice:id,referensi,nama_pelanggan', 'user:id,name'])
            ->latest('tanggal_retur')
            ->latest('id')
            ->paginate(20);
        $totalCases = ReturBarang::count();
        $totalQuantity = (int) ReturBarang::sum('kuantitas');
        $totalValue = (float) ReturBarang::sum('nilai_retur');

        $selectedInvoiceId = $request->query('invoice_id');
        $selectedItemIndex = $request->query('item_index');
        $selectedQuantity = max(1, (int) $request->query('kuantitas', 1));
        $selectedImeis = $this->parseImeis($request->query('detail_imei', ''));

        return view('penjualan.retur', compact(
            'invoiceOptions',
            'returns',
            'totalCases',
            'totalQuantity',
            'totalValue',
            'selectedInvoiceId',
            'selectedItemIndex',
            'selectedQuantity',
            'selectedImeis'
        ));
    }

    public function storeRetur(Request $request)
    {
        if (!$request->has('returns') && $request->has('item_index')) {
            $request->merge([
                'returns' => [[
                    'item_index' => $request->input('item_index'),
                    'kuantitas' => $request->input('kuantitas'),
                    'alasan' => $request->input('alasan'),
                    'kondisi' => $request->input('kondisi'),
                    'imeis' => $this->parseImeis($request->input('detail_imei', '')),
                    'catatan' => $request->input('catatan'),
                ]],
            ]);
        }

        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
            'returns' => 'required|array|min:1',
            'returns.*.item_index' => 'required|integer|min:0',
            'returns.*.kuantitas' => 'required|integer|min:1',
            'returns.*.alasan' => 'required|in:Tidak sesuai pesanan,Berubah pikiran,Cacat atau rusak,Lainnya',
            'returns.*.kondisi' => 'required|in:Layak jual,Perlu pemeriksaan,Rusak',
            'returns.*.imeis' => 'nullable|array',
            'returns.*.imeis.*' => 'string|max:100',
            'returns.*.catatan' => 'nullable|string|max:2000',
            'tanggal_retur' => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            $invoice = Invoice::whereKey($validated['invoice_id'])->lockForUpdate()->firstOrFail();

            if (!$invoice->is_locked) {
                throw ValidationException::withMessages([
                    'invoice_id' => 'Retur hanya dapat dicatat untuk invoice yang sudah terkunci.',
                ]);
            }

            $items = is_array($invoice->items) ? array_values($invoice->items) : [];
            $previousReturns = ReturBarang::where('invoice_id', $invoice->id)->get();
            $returnedQuantityByItem = [];
            $alreadyReturnedImeisByItem = [];
            foreach ($previousReturns as $previousReturn) {
                $itemIndex = (int) $previousReturn->item_index;
                $returnedQuantityByItem[$itemIndex] = ($returnedQuantityByItem[$itemIndex] ?? 0) + (int) $previousReturn->kuantitas;
                $alreadyReturnedImeisByItem[$itemIndex] = array_merge(
                    $alreadyReturnedImeisByItem[$itemIndex] ?? [],
                    array_map('strtolower', $this->parseImeis($previousReturn->detail_imei ?? ''))
                );
            }

            $requestedQuantityByItem = [];
            $requestedImeisByItem = [];
            foreach ($validated['returns'] as $rowIndex => $returnRow) {
                $itemIndex = (int) $returnRow['item_index'];
                $item = $items[$itemIndex] ?? null;
                if (!is_array($item)) {
                    throw ValidationException::withMessages([
                        "returns.{$rowIndex}.item_index" => 'Barang tidak ditemukan pada invoice yang dipilih.',
                    ]);
                }

                $details = $this->getInvoiceItemDetails($item);
                $requestedQuantityByItem[$itemIndex] = ($requestedQuantityByItem[$itemIndex] ?? 0) + (int) $returnRow['kuantitas'];
                $remaining = max(0, $details['quantity'] - ($returnedQuantityByItem[$itemIndex] ?? 0));
                if ($requestedQuantityByItem[$itemIndex] > $remaining) {
                    throw ValidationException::withMessages([
                        "returns.{$rowIndex}.kuantitas" => "Qty retur melebihi sisa untuk {$details['name']} ({$remaining} unit).",
                    ]);
                }

                $returnedImeis = array_values(array_unique(array_map('trim', $returnRow['imeis'] ?? [])));
                if ($details['imeis']) {
                    if (count($returnedImeis) !== (int) $returnRow['kuantitas']) {
                        throw ValidationException::withMessages([
                            "returns.{$rowIndex}.imeis" => 'Pilih IMEI sebanyak jumlah unit yang diretur.',
                        ]);
                    }

                    $uniqueImeis = array_map('strtolower', $returnedImeis);
                    if (count(array_unique($uniqueImeis)) !== count($uniqueImeis)) {
                        throw ValidationException::withMessages([
                            "returns.{$rowIndex}.imeis" => 'IMEI yang dipilih tidak boleh duplikat.',
                        ]);
                    }

                    $validImeis = array_map('strtolower', $details['imeis']);
                    foreach ($returnedImeis as $imei) {
                        $normalizedImei = strtolower($imei);
                        if (!in_array($normalizedImei, $validImeis, true)) {
                            throw ValidationException::withMessages([
                                "returns.{$rowIndex}.imeis" => "IMEI {$imei} tidak tercatat pada item invoice ini.",
                            ]);
                        }

                        if (
                            in_array($normalizedImei, $alreadyReturnedImeisByItem[$itemIndex] ?? [], true)
                            || in_array($normalizedImei, $requestedImeisByItem[$itemIndex] ?? [], true)
                        ) {
                            throw ValidationException::withMessages([
                                "returns.{$rowIndex}.imeis" => "IMEI {$imei} sudah dipilih pada retur lain.",
                            ]);
                        }
                    }
                    $requestedImeisByItem[$itemIndex] = array_merge($requestedImeisByItem[$itemIndex] ?? [], $uniqueImeis);
                } elseif ($returnedImeis) {
                    throw ValidationException::withMessages([
                        "returns.{$rowIndex}.imeis" => 'Invoice ini tidak memiliki IMEI/serial untuk item tersebut.',
                    ]);
                }

                $quantity = (int) $returnRow['kuantitas'];
                $returnCost = $this->calculateReturnCost($invoice, $item, $returnedImeis, $quantity);

                ReturBarang::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => Auth::id(),
                    'item_index' => $itemIndex,
                    'nama_barang' => $details['name'],
                    'kuantitas' => $quantity,
                    'harga_satuan' => $details['price'],
                    'nilai_retur' => $details['price'] * $quantity,
                    'nilai_modal' => $returnCost,
                    'alasan' => $returnRow['alasan'],
                    'kondisi' => $returnRow['kondisi'],
                    'detail_imei' => $returnedImeis ? implode("\n", $returnedImeis) : null,
                    'tanggal_retur' => $validated['tanggal_retur'],
                    'catatan' => $returnRow['catatan'] ?? null,
                ]);

                $this->markPurchaseItemsReturned($invoice, $item, $returnedImeis, $quantity);
            }
        });

        return redirect()->route('penjualan.retur.index')->with('success', 'Semua barang retur berhasil dicatat. Invoice asli tetap terkunci.');
    }

    private function getInvoiceItemDetails(array $item): array
    {
        $quantity = max(1, (int) ($item['kuantitas'] ?? count($this->getInvoiceItemImeis($item)) ?: 1));
        $price = $item['harga'] ?? $item['harga_jual'] ?? null;
        if (!is_numeric($price)) {
            $price = isset($item['jumlah']) && is_numeric($item['jumlah'])
                ? (float) $item['jumlah'] / $quantity
                : 0;
        }

        return [
            'name' => $item['nama_barang'] ?? $item['deskripsi'] ?? 'Barang Invoice',
            'quantity' => $quantity,
            'price' => (float) $price,
            'imeis' => $this->getInvoiceItemImeis($item),
        ];
    }

    private function markPurchaseItemsReturned(Invoice $invoice, array $item, array $returnedImeis, int $quantity): void
    {
        $purchaseIds = array_values(array_unique(array_map('intval', $item['pembelian_ids'] ?? [])));
        $snapshots = is_array($invoice->pembelian_data) ? $invoice->pembelian_data : [];
        $itemName = strtolower($item['nama_barang'] ?? $item['deskripsi'] ?? '');

        if (!$purchaseIds) {
            foreach ($snapshots as $snapshot) {
                if (
                    is_array($snapshot)
                    && strtolower((string) ($snapshot['nama_barang'] ?? '')) === $itemName
                    && !empty($snapshot['pembelian_id'])
                ) {
                    $purchaseIds[] = (int) $snapshot['pembelian_id'];
                }
            }
            $purchaseIds = array_values(array_unique($purchaseIds));
        }

        if (!$purchaseIds) {
            return;
        }

        if ($returnedImeis) {
            $wantedImeis = array_map('strtolower', $returnedImeis);
            $purchaseIdsToUpdate = [];
            foreach ($snapshots as $snapshot) {
                if (!is_array($snapshot) || !in_array((int) ($snapshot['pembelian_id'] ?? 0), $purchaseIds, true)) {
                    continue;
                }

                $snapshotImeis = array_map('strtolower', $this->getInvoiceItemImeis($snapshot));
                if (array_intersect($wantedImeis, $snapshotImeis)) {
                    $purchaseIdsToUpdate[] = (int) $snapshot['pembelian_id'];
                }
            }

            if (!$purchaseIdsToUpdate) {
                foreach (Pembelian::whereIn('id', $purchaseIds)->get(['id', 'detail_imei']) as $pembelian) {
                    $purchaseImeis = array_map('strtolower', $this->parseImeis($pembelian->detail_imei ?? ''));
                    if (array_intersect($wantedImeis, $purchaseImeis)) {
                        $purchaseIdsToUpdate[] = $pembelian->id;
                    }
                }
            }
        } else {
            $purchaseIdsToUpdate = Pembelian::whereIn('id', $purchaseIds)
                ->where('status', '!=', 'Retur')
                ->orderBy('id')
                ->limit($quantity)
                ->pluck('id')
                ->all();
        }

        if ($purchaseIdsToUpdate) {
            Pembelian::whereIn('id', array_unique($purchaseIdsToUpdate))->update(['status' => 'Retur']);
        }
    }

    private function getInvoiceItemImeis(array $item): array
    {
        if (isset($item['imei_list']) && is_array($item['imei_list'])) {
            return array_values(array_filter(array_map('trim', $item['imei_list'])));
        }

        return $this->parseImeis(
            $item['deskripsi_imei'] ?? $item['detail_imei'] ?? $item['imei'] ?? ''
        );
    }

    private function calculateReturnCost(Invoice $invoice, array $item, array $returnedImeis, int $quantity): float
    {
        $snapshots = is_array($invoice->pembelian_data) ? $invoice->pembelian_data : [];
        $purchaseIds = array_map('intval', $item['pembelian_ids'] ?? []);
        $itemName = strtolower($item['nama_barang'] ?? $item['deskripsi'] ?? '');
        $matchingSnapshots = array_values(array_filter($snapshots, function ($snapshot) use ($purchaseIds, $itemName) {
            if (!is_array($snapshot)) {
                return false;
            }

            if ($purchaseIds && isset($snapshot['pembelian_id'])) {
                return in_array((int) $snapshot['pembelian_id'], $purchaseIds, true);
            }

            return strtolower((string) ($snapshot['nama_barang'] ?? '')) === $itemName;
        }));

        $unitCostByImei = [];
        $weightedCost = 0.0;
        $unitCount = 0;
        foreach ($matchingSnapshots as $snapshot) {
            $modal = (float) ($snapshot['total_modal'] ?? 0);
            $imeis = $this->getInvoiceItemImeis($snapshot);
            $snapshotQuantity = max(1, count($imeis));
            $weightedCost += $modal * $snapshotQuantity;
            $unitCount += $snapshotQuantity;

            foreach ($imeis as $imei) {
                $unitCostByImei[strtolower($imei)] = $modal;
            }
        }

        if ($returnedImeis) {
            $matchedCost = 0.0;
            foreach ($returnedImeis as $imei) {
                if (!array_key_exists(strtolower($imei), $unitCostByImei)) {
                    $matchedCost = 0.0;
                    break;
                }

                $matchedCost += $unitCostByImei[strtolower($imei)];
            }

            if ($matchedCost > 0) {
                return $matchedCost;
            }
        }

        return $unitCount > 0 ? ($weightedCost / $unitCount) * $quantity : 0.0;
    }

    private function parseImeis(?string $value): array
    {
        if (empty($value) || $value === '-') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $value) ?: [])));
    }

    public function detailBarang(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        $tokos   = Toko::orderBy('nama_toko', 'asc')->get();
        $devices = Device::orderBy('nama_device', 'asc')->get();

        $detailBarangs = Pembelian::where('status', 'Selesai')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('kode_otomatis', 'like', "%{$search}%")
                        ->orWhere('kode_manual', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_device', 'like', "%{$search}%")
                        ->orWhere('nama_toko', 'like', "%{$search}%")
                        ->orWhere('detail_imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('penjualan.detail_barang', compact('detailBarangs', 'search', 'barangs', 'tokos', 'devices'));
    }

    /**
     * Update data detail barang & lampiran
     */
    public function updateDetailBarang(Request $request, $id)
    {
        $request->validate([
            'nama_barang'     => 'required|string|max:255',
            'nama_device'     => 'nullable|string|max:255',
            'nama_toko'       => 'required|string|max:255',
            'via'             => 'required|string|max:255',
            'tanggal_beli'    => 'required|date',
            'total_modal'     => 'required|numeric',
            'detail_imei'     => 'nullable|string|max:250',
            'status'          => 'required|in:Belum Ready,Sudah Ready,Sudah Diambil,Bermasalah,Jual',
            'file_lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat diubah.');
        }

        $data = $request->except(['file_lampiran', 'delete_files']);
        $data['detail_imei'] = $request->input('detail_imei');

        $existingFiles = $pembelian->file_lampiran ?? [];

        if ($request->has('delete_files')) {
            $filesToDelete = $request->input('delete_files');
            $remainingFiles = [];

            foreach ($existingFiles as $index => $filePath) {
                if (in_array($index, $filesToDelete)) {
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                } else {
                    $remainingFiles[] = $filePath;
                }
            }
            $existingFiles = $remainingFiles;
        }

        if ($request->hasFile('file_lampiran')) {
            foreach ($request->file('file_lampiran') as $file) {
                $existingFiles[] = $file->store('lampiran_pembelian', 'public');
            }
        }

        $data['file_lampiran'] = array_values($existingFiles);
        $pembelian->update($data);

        return redirect()->back()->with('success', 'Data Detail Barang & IMEI berhasil diperbarui!');
    }

    /**
     * Hapus data detail barang beserta file lampirannya
     */
    public function destroyDetailBarang($id)
    {
        $pembelian = Pembelian::findOrFail($id);

        if (Invoice::isLockedForPurchase($pembelian)) {
            return redirect()->back()->with('error', 'Data ini terkunci oleh invoice dan tidak dapat dihapus.');
        }

        if (!empty($pembelian->file_lampiran) && is_array($pembelian->file_lampiran)) {
            foreach ($pembelian->file_lampiran as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        }

        $pembelian->delete();

        return redirect()->back()->with('success', 'Data Detail Barang berhasil dihapus!');
    }

    /**
     * Mengunci invoice agar data dan statusnya tidak dapat diubah lagi.
     */
    public function lockInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->is_locked) {
            return redirect()->back()->with('error', 'Invoice ini sudah terkunci.');
        }

        if (strtolower($invoice->status_payment ?? '') !== 'sudah') {
            return redirect()->back()->with('error', 'Invoice hanya dapat dikunci setelah status payment menjadi Sudah.');
        }

        $invoice->update(['is_locked' => true]);

        return redirect()->back()->with('success', 'Invoice berhasil dikunci dan tidak dapat diubah lagi.');
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'status_payment' => 'required|in:belum,sudah',
        ]);

        $invoice = Invoice::findOrFail($id);
        if ($invoice->is_locked) {
            return redirect()->back()->with('error', 'Invoice ini terkunci dan status pembayarannya tidak dapat diubah.');
        }

        $statusInput = strtolower($request->status_payment);
        $tanggalPayment = ($statusInput === 'sudah') ? Carbon::now()->toDateString() : null;

        // Eksekusi update langsung ke Query Builder
        DB::table('invoices')
            ->where('id', $id)
            ->update([
                'status_payment'  => $statusInput,
                'tanggal_payment' => $tanggalPayment,
                'updated_at'      => Carbon::now(),
            ]);

        return redirect()->back()->with('success', 'Status payment berhasil diperbarui menjadi ' . strtoupper($statusInput) . '!');
    }

    /**
     * Memperbarui rincian item pada invoice lama/histori secara manual
     */
    public function updateHistoriItem(Request $request, $invoiceId)
    {
        $request->validate([
            'item_index'   => 'required|integer',
            'nama_barang'  => 'required|string|max:255',
            'nama_device'  => 'nullable|string|max:255',
            'nama_toko'    => 'required|string|max:255',
            'via'          => 'required|string|max:255',
            'detail_imei'  => 'nullable|string|max:250',
            'total_modal'  => 'required|numeric',
        ]);

        $invoice = Invoice::findOrFail($invoiceId);

        if ($invoice->is_locked) {
            return redirect()->back()->with('error', 'Invoice ini terkunci dan rinciannya tidak dapat diubah.');
        }

        $isSnapshot = !empty($invoice->pembelian_data);
        $items = $isSnapshot ? $invoice->pembelian_data : ($invoice->items ?? []);

        $index = $request->input('item_index');

        if (isset($items[$index])) {
            $items[$index]['nama_barang'] = $request->input('nama_barang');
            $items[$index]['nama_device'] = $request->input('nama_device');
            $items[$index]['nama_toko']   = $request->input('nama_toko');
            $items[$index]['via']         = $request->input('via');
            $items[$index]['detail_imei'] = $request->input('detail_imei');
            $items[$index]['total_modal'] = $request->input('total_modal');

            if ($isSnapshot) {
                DB::table('invoices')->where('id', $invoiceId)->update(['pembelian_data' => json_encode($items)]);
            } else {
                DB::table('invoices')->where('id', $invoiceId)->update(['items' => json_encode($items)]);
            }

            return redirect()->back()->with('success', 'Rincian data item histori berhasil diperbarui!');
        }

        return redirect()->back()->with('error', 'Item rincian tidak ditemukan.');
    }
}
