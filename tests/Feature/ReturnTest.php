<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Pembelian;
use App\Models\ReturBarang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_return_page_displays_locked_invoice_items(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);

        $response = $this->actingAs($user)->get(route('penjualan.retur.index'));

        $response->assertOk();
        $response->assertSee('Catat retur baru');
        $response->assertSee($invoice->referensi);
        $response->assertSee('IMEI / serial');

        $this->actingAs($user)
            ->get(route('penjualan.retur.index', [
                'invoice_id' => $invoice->id,
                'item_index' => 0,
                'kuantitas' => 1,
                'detail_imei' => '111111111111111',
            ]))
            ->assertOk()
            ->assertSee('data-selected-item="0"', false)
            ->assertSee('data-selected-quantity="1"', false)
            ->assertSee('data-selected-imeis=', false)
            ->assertSee('111111111111111');
    }

    public function test_locked_invoice_return_is_recorded_without_changing_invoice(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);
        $pembelian = Pembelian::create([
            'kode_otomatis' => 'TRX-RETUR-STATUS-001',
            'nama_barang' => 'Telepon Uji',
            'nama_toko' => 'Toko Uji',
            'via' => 'Tokopedia',
            'tanggal_beli' => '2026-08-30',
            'total_modal' => 500000,
            'status' => 'Selesai',
            'detail_imei' => '111111111111111',
        ]);
        $pembelianLain = Pembelian::create([
            'kode_otomatis' => 'TRX-RETUR-STATUS-002',
            'nama_barang' => 'Telepon Uji',
            'nama_toko' => 'Toko Uji',
            'via' => 'Tokopedia',
            'tanggal_beli' => '2026-08-30',
            'total_modal' => 500000,
            'status' => 'Selesai',
            'detail_imei' => '222222222222222',
        ]);
        $items = $invoice->items;
        $itemKey = array_key_first($items);
        $items[$itemKey]['pembelian_ids'] = [$pembelian->id, $pembelianLain->id];
        $purchaseSnapshots = [
            [
                'pembelian_id' => $pembelian->id,
                'nama_barang' => 'Telepon Uji',
                'detail_imei' => '111111111111111',
                'total_modal' => 500000,
                'harga_jual' => 1500000,
                'total_profit' => 1000000,
            ],
            [
                'pembelian_id' => $pembelianLain->id,
                'nama_barang' => 'Telepon Uji',
                'detail_imei' => '222222222222222',
                'total_modal' => 500000,
                'harga_jual' => 1500000,
                'total_profit' => 1000000,
            ],
        ];
        $invoice->update([
            'items' => $items,
            'pembelian_data' => $purchaseSnapshots,
        ]);

        $response = $this->actingAs($user)->post(route('penjualan.retur.store'), [
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'kuantitas' => 1,
            'alasan' => 'Cacat atau rusak',
            'kondisi' => 'Rusak',
            'detail_imei' => '111111111111111',
            'tanggal_retur' => '2026-09-30',
            'catatan' => 'Layar tidak menyala',
        ]);

        $response->assertRedirect(route('penjualan.retur.index'));
        $this->assertDatabaseHas('retur_barangs', [
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'kuantitas' => 1,
            'harga_satuan' => 1500000,
            'nilai_retur' => 1500000,
            'nilai_modal' => 500000,
            'detail_imei' => '111111111111111',
        ]);
        $this->assertTrue($invoice->fresh()->is_locked);
        $this->assertSame(2, array_values($invoice->fresh()->items)[0]['kuantitas']);
        $this->assertSame('Retur', $pembelian->fresh()->status);
        $this->assertSame('Selesai', $pembelianLain->fresh()->status);

        $this->actingAs($user)
            ->get(route('pembelian.index', ['status' => 'Retur']))
            ->assertOk()
            ->assertSee('TRX-RETUR-STATUS-001')
            ->assertSee('Retur');

        $this->actingAs($user)
            ->get(route('pembelian.histori_rekap'))
            ->assertOk()
            ->assertSee('TRX-RETUR-STATUS-001')
            ->assertSee('Retur');
    }

    public function test_sales_history_marks_returned_and_not_returned_units(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);
        ReturBarang::create([
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'nama_barang' => 'Telepon Uji',
            'kuantitas' => 1,
            'harga_satuan' => 1500000,
            'nilai_retur' => 1500000,
            'nilai_modal' => 500000,
            'alasan' => 'Cacat atau rusak',
            'kondisi' => 'Rusak',
            'detail_imei' => '111111111111111',
            'tanggal_retur' => '2026-09-30',
        ]);

        $this->actingAs($user)
            ->get(route('penjualan.histori'))
            ->assertOk()
            ->assertDontSee('Status retur per barang')
            ->assertSee('Pilih unit yang akan diretur')
            ->assertSee('aria-label="Pilih IMEI 222222222222222 untuk retur"', false)
            ->assertSee('aria-label="IMEI 111111111111111 sudah diretur"', false)
            ->assertSee('data-imei="222222222222222"', false);
    }

    public function test_unlocked_invoice_cannot_receive_a_return(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(false);

        $response = $this->actingAs($user)
            ->from(route('penjualan.retur.index'))
            ->post(route('penjualan.retur.store'), [
                'invoice_id' => $invoice->id,
                'item_index' => 0,
                'kuantitas' => 1,
                'alasan' => 'Cacat atau rusak',
                'kondisi' => 'Rusak',
                'detail_imei' => '111111111111111',
                'tanggal_retur' => '2026-09-30',
            ]);

        $response->assertSessionHasErrors('invoice_id');
        $this->assertDatabaseCount('retur_barangs', 0);
    }

    public function test_multiple_items_with_different_reasons_can_be_saved_together(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);
        $tabletPurchase = Pembelian::create([
            'kode_otomatis' => 'TRX-RETUR-TABLET-001',
            'nama_barang' => 'Tablet Uji',
            'nama_toko' => 'Toko Uji',
            'via' => 'Tokopedia',
            'tanggal_beli' => '2026-08-30',
            'total_modal' => 300000,
            'status' => 'Selesai',
        ]);
        $items = $invoice->items;
        $items['tablet-uji_900000'] = [
            'nama_barang' => 'Tablet Uji',
            'pembelian_ids' => [$tabletPurchase->id],
            'kuantitas' => 1,
            'harga' => 900000,
            'jumlah' => 900000,
            'deskripsi_imei' => '-',
        ];
        $invoice->update(['items' => $items]);

        $response = $this->actingAs($user)->post(route('penjualan.retur.store'), [
            'invoice_id' => $invoice->id,
            'tanggal_retur' => '2026-09-30',
            'returns' => [
                [
                    'item_index' => 0,
                    'kuantitas' => 1,
                    'imeis' => ['111111111111111'],
                    'alasan' => 'Cacat atau rusak',
                    'kondisi' => 'Rusak',
                    'catatan' => 'Layar bermasalah',
                ],
                [
                    'item_index' => 1,
                    'kuantitas' => 1,
                    'alasan' => 'Tidak sesuai pesanan',
                    'kondisi' => 'Layak jual',
                    'catatan' => 'Salah pilih warna',
                ],
            ],
        ]);

        $response->assertRedirect(route('penjualan.retur.index'));
        $this->assertDatabaseCount('retur_barangs', 2);
        $this->assertDatabaseHas('retur_barangs', [
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'alasan' => 'Cacat atau rusak',
            'detail_imei' => '111111111111111',
        ]);
        $this->assertDatabaseHas('retur_barangs', [
            'invoice_id' => $invoice->id,
            'item_index' => 1,
            'nama_barang' => 'Tablet Uji',
            'alasan' => 'Tidak sesuai pesanan',
        ]);
        $this->assertSame('Retur', $tabletPurchase->fresh()->status);
    }

    public function test_dashboard_sales_total_reflects_returns(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);
        ReturBarang::create([
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'nama_barang' => 'Telepon Uji',
            'kuantitas' => 2,
            'harga_satuan' => 1500000,
            'nilai_retur' => 3000000,
            'nilai_modal' => 1000000,
            'alasan' => 'Cacat atau rusak',
            'kondisi' => 'Rusak',
            'detail_imei' => "111111111111111\n222222222222222",
            'tanggal_retur' => '2026-09-30',
        ]);
        $invoice->update(['tanggal_payment' => '2026-09-05']);

        $this->actingAs($user)
            ->get('/rekap?start_date_penjualan=2026-09-01&end_date_penjualan=2026-09-30&start_date_profit=2026-09-01&end_date_profit=2026-09-30')
            ->assertOk()
            ->assertViewHas('totalPenjualan', 0.0)
            ->assertViewHas('totalProfit', 0.0);

        $this->actingAs($user)
            ->get('/keuangan?tanggal=2026-09-01')
            ->assertOk()
            ->assertViewHas('totalProfitNominal', 0.0);

        $this->actingAs($user)
            ->get('/keuangan?tanggal=2026-09-30')
            ->assertOk()
            ->assertViewHas('totalProfitNominal', 0.0);
    }

    public function test_return_cannot_exceed_remaining_quantity_or_repeat_an_imei(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);
        $this->actingAs($user)->post(route('penjualan.retur.store'), [
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'kuantitas' => 1,
            'alasan' => 'Cacat atau rusak',
            'kondisi' => 'Rusak',
            'detail_imei' => '111111111111111',
            'tanggal_retur' => '2026-09-30',
        ])->assertRedirect(route('penjualan.retur.index'));

        $this->from(route('penjualan.retur.index'))->post(route('penjualan.retur.store'), [
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'kuantitas' => 1,
            'alasan' => 'Berubah pikiran',
            'kondisi' => 'Layak jual',
            'detail_imei' => '111111111111111',
            'tanggal_retur' => '2026-09-30',
        ])->assertSessionHasErrors('returns.0.imeis');

        $this->from(route('penjualan.retur.index'))->post(route('penjualan.retur.store'), [
            'invoice_id' => $invoice->id,
            'item_index' => 0,
            'kuantitas' => 2,
            'alasan' => 'Berubah pikiran',
            'kondisi' => 'Layak jual',
            'detail_imei' => "111111111111111\n222222222222222",
            'tanggal_retur' => '2026-09-30',
        ])->assertSessionHasErrors('returns.0.kuantitas');

        $this->assertSame(1, ReturBarang::sum('kuantitas'));
    }

    public function test_one_return_cannot_repeat_an_imei_within_the_request(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $invoice = $this->createInvoice(true);

        $this->actingAs($user)
            ->from(route('penjualan.retur.index'))
            ->post(route('penjualan.retur.store'), [
                'invoice_id' => $invoice->id,
                'item_index' => 0,
                'kuantitas' => 2,
                'alasan' => 'Cacat atau rusak',
                'kondisi' => 'Rusak',
                'detail_imei' => "111111111111111\n111111111111111",
                'tanggal_retur' => '2026-09-30',
            ])
            ->assertSessionHasErrors('returns.0.imeis');

        $this->assertDatabaseCount('retur_barangs', 0);
    }

    private function createInvoice(bool $locked): Invoice
    {
        return Invoice::create([
            'referensi' => $locked ? 'INV/RETUR/LOCKED' : 'INV/RETUR/OPEN',
            'tanggal' => '2026-09-01',
            'jatuh_tempo' => '2026-09-30',
            'nama_pelanggan' => 'Pelanggan Uji',
            'alamat_pelanggan' => 'Bekasi',
            'items' => ['telepon-uji_1500000' => [
                'nama_barang' => 'Telepon Uji',
                'kuantitas' => 2,
                'harga' => 1500000,
                'jumlah' => 3000000,
                'deskripsi_imei' => "111111111111111\n222222222222222",
            ]],
            'pembelian_data' => [
                [
                    'nama_barang' => 'Telepon Uji',
                    'detail_imei' => '111111111111111',
                    'total_modal' => 500000,
                    'harga_jual' => 1500000,
                    'total_profit' => 1000000,
                ],
                [
                    'nama_barang' => 'Telepon Uji',
                    'detail_imei' => '222222222222222',
                    'total_modal' => 500000,
                    'harga_jual' => 1500000,
                    'total_profit' => 1000000,
                ],
            ],
            'subtotal' => 3000000,
            'total' => 3000000,
            'terbilang' => 'Tiga Juta Rupiah',
            'status_payment' => 'sudah',
            'tanggal_payment' => '2026-09-01',
            'is_locked' => $locked,
        ]);
    }
}
