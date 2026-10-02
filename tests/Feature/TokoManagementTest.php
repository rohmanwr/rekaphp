<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_can_be_created_without_a_code_when_location_is_provided(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('toko.store'), [
            'kode_toko' => '',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => 'Jakarta Pusat',
            'link_toko' => '',
        ]);

        $response->assertRedirect(route('toko.index'));
        $this->assertDatabaseHas('tokos', [
            'kode_toko' => null,
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => 'Jakarta Pusat',
        ]);
    }

    public function test_store_can_be_created_without_a_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('toko.store'), [
            'kode_toko' => 'TK-NO-LOCATION',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => '',
        ]);

        $response->assertRedirect(route('toko.index'));
        $this->assertDatabaseHas('tokos', [
            'kode_toko' => 'TK-NO-LOCATION',
            'nama_toko' => 'Toko Uji',
            'lokasi_toko' => null,
        ]);
    }
}
