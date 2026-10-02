<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_user_from_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('user.store'), [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'abc',
            'password_confirmation' => 'abc',
            'role' => 'user',
        ]);

        $response->assertRedirect(route('user.index'));

        $user = User::where('username', 'newuser')->firstOrFail();
        $this->assertSame('New User', $user->name);
        $this->assertSame('user', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('abc', $user->password));
    }
}
