<?php

namespace Tests\Feature;

use App\Models\Kabupaten;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createPopt(Uppt $uppt): User
    {
        return User::factory()->create([
            'role' => 'popt',
            'uppt_id' => $uppt->id,
        ]);
    }

    private function createUppt(): Uppt
    {
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Test']);
        return Uppt::create(['nama_uppt' => 'UPPT Test', 'kabupaten_id' => $kabupaten->id]);
    }

    public function test_forbids_popt_from_accessing_index(): void
    {
        $response = $this->actingAs($this->createPopt($this->createUppt()))->get('/pengguna');
        
        $response->assertForbidden();
    }

    public function test_renders_index_for_admin(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/pengguna');
        
        $response->assertOk();
        $response->assertViewIs('master.user.index');
    }

    public function test_rejects_creation_with_invalid_data(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/pengguna', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'role' => 'popt',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'uppt_id']);
    }

    public function test_creates_popt_user_and_redirects(): void
    {
        $uppt = $this->createUppt();

        $response = $this->actingAs($this->createAdmin())->post('/pengguna', [
            'name' => 'Petugas POPT Baru',
            'email' => 'poptbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'popt',
            'uppt_id' => $uppt->id,
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'poptbaru@example.com',
            'role' => 'popt',
            'uppt_id' => $uppt->id,
            'status' => 'aktif',
        ]);
    }

    public function test_creates_admin_user_and_ignores_uppt_id(): void
    {
        $uppt = $this->createUppt();

        $response = $this->actingAs($this->createAdmin())->post('/pengguna', [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'uppt_id' => $uppt->id,
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@example.com',
            'role' => 'admin',
            'uppt_id' => null,
            'status' => 'aktif',
        ]);
    }

    public function test_updates_user_without_changing_password(): void
    {
        $uppt = $this->createUppt();
        $userTarget = User::factory()->create([
            'name' => 'User Lama',
            'email' => 'lama@example.com',
            'role' => 'popt',
            'uppt_id' => $uppt->id,
            'password' => Hash::make('password_lama'),
        ]);

        $response = $this->actingAs($this->createAdmin())->put("/pengguna/{$userTarget->id}", [
            'name' => 'User Update',
            'email' => 'update@example.com',
            'role' => 'popt',
            'status' => 'aktif',
            'uppt_id' => $uppt->id,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $userTarget->id,
            'name' => 'User Update',
            'email' => 'update@example.com',
        ]);

        $userTarget->refresh();
        $this->assertTrue(Hash::check('password_lama', $userTarget->password));
    }

    public function test_rejects_deleting_own_account(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete("/pengguna/{$admin->id}");

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_deletes_other_user(): void
    {
        $userTarget = User::factory()->create();

        $response = $this->actingAs($this->createAdmin())->delete("/pengguna/{$userTarget->id}");

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $userTarget->id,
        ]);
    }

    public function test_updates_user_status(): void
    {
        $userTarget = User::factory()->create(['status' => 'nonaktif']);

        $response = $this->actingAs($this->createAdmin())->patch("/pengguna/{$userTarget->id}/status", [
            'status' => 'aktif',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $userTarget->id,
            'status' => 'aktif',
        ]);
    }
}
