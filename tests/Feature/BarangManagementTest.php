<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_barang_code_is_generated_and_hidden_from_master_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('barang.store'), [
            'kode_barang' => 'BRG-MANUAL',
            'nama_barang' => 'iPhone Uji',
            'harga_jual' => '12500000',
        ]);

        $response->assertRedirect(route('barang.index'));
        $barang = Barang::firstOrFail();
        $this->assertSame('BRG-00001', $barang->kode_barang);

        $page = $this->actingAs($admin)->get(route('barang.index'));
        $page->assertOk()
            ->assertSee('iPhone Uji')
            ->assertSee('Rp 12.500.000')
            ->assertDontSee('BRG-00001')
            ->assertDontSee('Kode Barang');
    }
}
