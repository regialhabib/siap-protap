<?php

namespace Tests\Feature;

use App\Models\Komoditas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomoditasTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createPopt(): User
    {
        return User::factory()->create(['role' => 'popt']);
    }

    public function test_forbids_popt_from_accessing_index(): void
    {
        $response = $this->actingAs($this->createPopt())->get('/komoditas');
        
        $response->assertForbidden();
    }

    public function test_renders_index_for_admin(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/komoditas');
        
        $response->assertOk();
        $response->assertViewIs('master.komoditas.index');
    }

    public function test_renders_filtered_index_based_on_search(): void
    {
        Komoditas::create(['nama_komoditas' => 'Padi Sawah']);
        Komoditas::create(['nama_komoditas' => 'Jagung']);

        $response = $this->actingAs($this->createAdmin())->get('/komoditas?search=Padi');

        $response->assertOk();
        $response->assertSee('Padi Sawah');
    }

    public function test_rejects_creation_when_nama_komoditas_is_empty(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/komoditas', []);
        
        $response->assertSessionHasErrors(['nama_komoditas']);
    }

    public function test_rejects_creation_when_nama_komoditas_is_duplicate(): void
    {
        Komoditas::create(['nama_komoditas' => 'Kedelai']);

        $response = $this->actingAs($this->createAdmin())->post('/komoditas', [
            'nama_komoditas' => 'Kedelai',
        ]);
        
        $response->assertSessionHasErrors(['nama_komoditas']);
    }

    public function test_creates_komoditas_and_redirects(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/komoditas', [
            'nama_komoditas' => 'Padi Gogo',
        ]);

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('komoditas', [
            'nama_komoditas' => 'Padi Gogo',
        ]);
    }

    public function test_updates_komoditas_and_redirects(): void
    {
        $komoditas = Komoditas::create(['nama_komoditas' => 'Bawang Merah']);

        $response = $this->actingAs($this->createAdmin())->put("/komoditas/{$komoditas->id}", [
            'nama_komoditas' => 'Bawang Putih',
        ]);

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('komoditas', [
            'id' => $komoditas->id,
            'nama_komoditas' => 'Bawang Putih',
        ]);
    }

    public function test_deletes_komoditas_and_redirects(): void
    {
        $komoditas = Komoditas::create(['nama_komoditas' => 'Cabai']);

        $response = $this->actingAs($this->createAdmin())->delete("/komoditas/{$komoditas->id}");

        $response->assertRedirect(route('komoditas.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('komoditas', [
            'id' => $komoditas->id,
        ]);
    }
}
