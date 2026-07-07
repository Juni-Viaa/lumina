<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'username'              => 'testuser',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard.index'));
    }

    public function test_registration_requires_username(): void
    {
        $response = $this->post('/register', [
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'username'              => 'newuser',
            'email'                 => 'taken@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}