<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Uppt;
use App\Models\Kabupaten;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $popt;
    protected $uppt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Test']);
        $this->uppt = Uppt::create(['nama_uppt' => 'UPPT Test', 'kabupaten_id' => $kabupaten->id]);
        $this->popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $this->uppt->id]);
    }

    public function test_only_admin_can_access_pengguna_index()
    {
        // POPT tidak bisa akses
        $responsePopt = $this->actingAs($this->popt)->get('/pengguna');
        $responsePopt->assertStatus(403);

        // Admin bisa akses
        $responseAdmin = $this->actingAs($this->admin)->get('/pengguna');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('master.user.index');
    }

    public function test_validation_fails_on_store_pengguna()
    {
        $response = $this->actingAs($this->admin)->post('/pengguna', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'role' => 'popt',
            // uppt_id tidak diisi padahal role = popt
        ]);
        
        $response->assertSessionHasErrors(['name', 'email', 'password', 'uppt_id']);
    }

    public function test_admin_can_store_pengguna_popt()
    {
        $response = $this->actingAs($this->admin)->post('/pengguna', [
            'name' => 'Petugas POPT Baru',
            'email' => 'poptbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'popt',
            'uppt_id' => $this->uppt->id
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'email' => 'poptbaru@example.com',
            'role' => 'popt',
            'uppt_id' => $this->uppt->id,
            'status' => 'aktif'
        ]);
    }

    public function test_admin_can_store_pengguna_admin()
    {
        $response = $this->actingAs($this->admin)->post('/pengguna', [
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
            'uppt_id' => $this->uppt->id // ini harusnya diabaikan oleh controller
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'email' => 'adminbaru@example.com',
            'role' => 'admin',
            'uppt_id' => null, // dipastikan null oleh controller
            'status' => 'aktif'
        ]);
    }

    public function test_admin_can_update_pengguna_without_changing_password()
    {
        $userTarget = User::factory()->create([
            'name' => 'User Lama',
            'email' => 'lama@example.com',
            'role' => 'popt',
            'uppt_id' => $this->uppt->id,
            'password' => Hash::make('password_lama')
        ]);

        $response = $this->actingAs($this->admin)->put("/pengguna/{$userTarget->id}", [
            'name' => 'User Update',
            'email' => 'update@example.com',
            'role' => 'popt',
            'status' => 'aktif',
            'uppt_id' => $this->uppt->id,
            'password' => '', // kosong, tidak diupdate
            'password_confirmation' => ''
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('users', [
            'id' => $userTarget->id,
            'name' => 'User Update',
            'email' => 'update@example.com'
        ]);

        // Verifikasi password lama masih berlaku
        $userTarget->refresh();
        $this->assertTrue(Hash::check('password_lama', $userTarget->password));
    }

    public function test_admin_cannot_delete_own_account()
    {
        $response = $this->actingAs($this->admin)->delete("/pengguna/{$this->admin->id}");

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id
        ]);
    }

    public function test_admin_can_delete_other_user()
    {
        $userTarget = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/pengguna/{$userTarget->id}");

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('users', [
            'id' => $userTarget->id
        ]);
    }

    public function test_admin_can_update_status_of_user()
    {
        $userTarget = User::factory()->create(['status' => 'nonaktif']);

        $response = $this->actingAs($this->admin)->patch("/pengguna/{$userTarget->id}/status", [
            'status' => 'aktif'
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $userTarget->id,
            'status' => 'aktif'
        ]);
    }
}
