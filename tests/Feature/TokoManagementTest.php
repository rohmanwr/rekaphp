<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_is_given_an_automatic_code_and_shows_its_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('toko.store'), [
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => 'Jakarta Pusat',
            'link_toko' => 'https://example.com/toko-uji',
        ]);

        $response->assertRedirect(route('toko.index'));
        $this->assertDatabaseHas('tokos', [
            'kode_toko' => 'TK-00001',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => 'Jakarta Pusat',
            'link_toko' => 'https://example.com/toko-uji',
        ]);

        $page = $this->actingAs($admin)->get(route('toko.index'));
        $page->assertOk()
            ->assertSee('Jakarta Pusat')
            ->assertSee('https://example.com/toko-uji')
            ->assertDontSee('Kode Toko');
    }

    public function test_store_code_is_generated_and_cannot_be_changed_by_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('toko.store'), [
            'kode_toko' => 'TK-MANUAL',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => '',
        ]);

        $response->assertRedirect(route('toko.index'));
        $this->assertDatabaseHas('tokos', [
            'kode_toko' => 'TK-00001',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => null,
        ]);

        $toko = \App\Models\Toko::firstOrFail();
        $this->actingAs($admin)->put(route('toko.update', $toko->id), [
            'kode_toko' => 'TK-ALTERED',
            'nama_toko' => 'Toko Uji Diperbarui',
            'lokasi_toko' => '',
        ])->assertRedirect(route('toko.index'));

        $this->assertDatabaseHas('tokos', [
            'id' => $toko->id,
            'kode_toko' => 'TK-00001',
            'nama_toko' => 'Toko Uji Diperbarui',
        ]);
    }
}
