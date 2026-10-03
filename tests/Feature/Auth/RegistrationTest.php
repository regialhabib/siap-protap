<?php

namespace Tests\Feature\Auth;

use App\Models\Kabupaten;
use App\Models\Uppt;
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
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test Uppt', 'kabupaten_id' => $kabupaten->id]);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'uppt_id' => $uppt->id,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => 'nonaktif',
            'role' => 'popt',
        ]);
    }
}
