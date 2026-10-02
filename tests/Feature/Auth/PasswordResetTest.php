<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_using_username(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'username' => $user->username,
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('abc', $user->fresh()->password));
    }

    public function test_password_reset_requires_at_least_three_characters(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $response = $this->post('/forgot-password', [
            'username' => $user->username,
            'password' => 'ab',
            'password_confirmation' => 'ab',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame($originalPassword, $user->fresh()->password);
    }
}
