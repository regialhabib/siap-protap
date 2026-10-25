<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page_with_login_link(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('SIAP-PROTAP')
            ->assertSee(route('login'))
            ->assertDontSee('Buka Dashboard');
    }

    public function test_authenticated_user_sees_dashboard_link_on_landing_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee('Buka Dashboard');
    }
}
