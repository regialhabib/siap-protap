<?php

namespace Tests\Feature;

use App\Models\Komoditas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomoditasTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $popt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->popt = User::factory()->create(['role' => 'popt']);
    }

    public function test_only_admin_can_access_komoditas_index()
    {
        // POPT tidak bisa akses
        $responsePopt = $this->actingAs($this->popt)->get('/komoditas');
        $responsePopt->assertStatus(403);

        // Admin bisa akses
        $responseAdmin = $this->actingAs($this->admin)->get('/komoditas');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('master.komoditas.index');
    }

    public function test_admin_can_search_komoditas()
    {
        Komoditas::create(['nama_komoditas' => 'Padi Sawah']);
        Komoditas::create(['nama_komoditas' => 'Jagung']);

        $response = $this->actingAs($this->admin)->get('/komoditas?search=Padi');

        $response->assertStatus(200);
        $response->assertSee('Padi Sawah');
        // search is now client-side, so all data is returned initially
    }

    public function test_validation_fails_on_store_komoditas()
    {
        Komoditas::create(['nama_komoditas' => 'Kedelai']);

        // Test validasi kosong
        $responseEmpty = $this->actingAs($this->admin)->post('/komoditas', []);
        $responseEmpty->assertSessionHasErrors(['nama_komoditas']);

        // Test validasi unik
        $responseDuplicate = $this->actingAs($this->admin)->post('/komoditas', [
            'nama_komoditas' => 'Kedelai',
        ]);
        $responseDuplicate->assertSessionHasErrors(['nama_komoditas']);
    }

    public function test_admin_can_store_komoditas()
    {
        $response = $this->actingAs($this->admin)->post('/komoditas', [
            'nama_komoditas' => 'Padi Gogo',
        ]);

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('komoditas', [
            'nama_komoditas' => 'Padi Gogo',
        ]);
    }

    public function test_admin_can_update_komoditas()
    {
        $komoditas = Komoditas::create(['nama_komoditas' => 'Bawang Merah']);

        $response = $this->actingAs($this->admin)->put("/komoditas/{$komoditas->id}", [
            'nama_komoditas' => 'Bawang Putih',
        ]);

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('komoditas', [
            'id' => $komoditas->id,
            'nama_komoditas' => 'Bawang Putih',
        ]);
    }

    public function test_admin_can_delete_komoditas()
    {
        $komoditas = Komoditas::create(['nama_komoditas' => 'Cabai']);

        $response = $this->actingAs($this->admin)->delete("/komoditas/{$komoditas->id}");

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('komoditas', [
            'id' => $komoditas->id,
        ]);
    }
}
