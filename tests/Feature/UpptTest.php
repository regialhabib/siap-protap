<?php

namespace Tests\Feature;

use App\Models\Kabupaten;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpptTest extends TestCase
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

    private function createKabupaten(): Kabupaten
    {
        return Kabupaten::create(['nama_kabupaten' => 'Kabupaten Test']);
    }

    public function test_forbids_popt_from_accessing_index(): void
    {
        $response = $this->actingAs($this->createPopt())->get('/uppt');
        
        $response->assertForbidden();
    }

    public function test_renders_index_for_admin(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/uppt');
        
        $response->assertOk();
        $response->assertViewIs('master.uppt.index');
    }

    public function test_rejects_creation_with_invalid_data(): void
    {
        $response = $this->actingAs($this->createAdmin())->post('/uppt', []);

        $response->assertSessionHasErrors(['nama_uppt', 'kabupaten_id']);
    }

    public function test_creates_uppt_and_kecamatans_and_redirects(): void
    {
        $kabupaten = $this->createKabupaten();

        $response = $this->actingAs($this->createAdmin())->post('/uppt', [
            'nama_uppt' => 'UPPT Wilayah I',
            'kabupaten_id' => $kabupaten->id,
            'kecamatans' => ['Kecamatan A', 'Kecamatan B', null, ''],
        ]);

        $response->assertRedirect(route('uppt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('uppts', [
            'nama_uppt' => 'UPPT Wilayah I',
            'kabupaten_id' => $kabupaten->id,
        ]);

        $uppt = Uppt::where('nama_uppt', 'UPPT Wilayah I')->first();

        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan A',
        ]);
        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan B',
        ]);
        $this->assertEquals(2, $uppt->kecamatans()->count());
    }

    public function test_updates_uppt_and_syncs_kecamatans(): void
    {
        $kabupaten = $this->createKabupaten();
        $uppt = Uppt::create([
            'nama_uppt' => 'UPPT Lama',
            'kabupaten_id' => $kabupaten->id,
        ]);
        $uppt->kecamatans()->create(['nama_kecamatan' => 'Kecamatan Lama 1']);
        $kabupatenBaru = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Baru']);

        $response = $this->actingAs($this->createAdmin())->put("/uppt/{$uppt->id}", [
            'nama_uppt' => 'UPPT Baru',
            'kabupaten_id' => $kabupatenBaru->id,
            'kecamatans' => ['Kecamatan Baru 1', 'Kecamatan Baru 2'],
        ]);

        $response->assertRedirect(route('uppt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('uppts', [
            'id' => $uppt->id,
            'nama_uppt' => 'UPPT Baru',
            'kabupaten_id' => $kabupatenBaru->id,
        ]);
        $this->assertDatabaseMissing('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan Lama 1',
        ]);
        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan Baru 1',
        ]);
    }

    public function test_deletes_uppt_and_redirects(): void
    {
        $kabupaten = $this->createKabupaten();
        $uppt = Uppt::create([
            'nama_uppt' => 'UPPT Hapus',
            'kabupaten_id' => $kabupaten->id,
        ]);
        $uppt->kecamatans()->create(['nama_kecamatan' => 'Kec Hapus']);

        $response = $this->actingAs($this->createAdmin())->delete("/uppt/{$uppt->id}");

        $response->assertRedirect(route('uppt.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('uppts', [
            'id' => $uppt->id,
        ]);
    }
}
