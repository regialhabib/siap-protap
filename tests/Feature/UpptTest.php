<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Uppt;
use App\Models\Kabupaten;

class UpptTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $popt;
    protected $kabupaten;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->popt = User::factory()->create(['role' => 'popt']);
        $this->kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Test']);
    }

    public function test_only_admin_can_access_uppt_index()
    {
        // POPT tidak bisa akses
        $responsePopt = $this->actingAs($this->popt)->get('/uppt');
        $responsePopt->assertStatus(403);

        // Admin bisa akses
        $responseAdmin = $this->actingAs($this->admin)->get('/uppt');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('master.uppt.index');
    }

    public function test_validation_fails_on_store_uppt()
    {
        $response = $this->actingAs($this->admin)->post('/uppt', []);
        
        $response->assertSessionHasErrors(['nama_uppt', 'kabupaten_id']);
    }

    public function test_admin_can_store_uppt_with_kecamatans()
    {
        $response = $this->actingAs($this->admin)->post('/uppt', [
            'nama_uppt' => 'UPPT Wilayah I',
            'kabupaten_id' => $this->kabupaten->id,
            'kecamatans' => ['Kecamatan A', 'Kecamatan B', null, ''] // Menguji array_filter
        ]);

        $response->assertRedirect(route('uppt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('uppts', [
            'nama_uppt' => 'UPPT Wilayah I',
            'kabupaten_id' => $this->kabupaten->id,
        ]);

        $uppt = Uppt::where('nama_uppt', 'UPPT Wilayah I')->first();
        
        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan A'
        ]);
        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan B'
        ]);
        
        // Memastikan yang kosong tidak tersimpan
        $this->assertEquals(2, $uppt->kecamatans()->count());
    }

    public function test_admin_can_update_uppt_and_sync_kecamatans()
    {
        $uppt = Uppt::create([
            'nama_uppt' => 'UPPT Lama',
            'kabupaten_id' => $this->kabupaten->id
        ]);
        $uppt->kecamatans()->create(['nama_kecamatan' => 'Kecamatan Lama 1']);

        $kabupatenBaru = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Baru']);

        $response = $this->actingAs($this->admin)->put("/uppt/{$uppt->id}", [
            'nama_uppt' => 'UPPT Baru',
            'kabupaten_id' => $kabupatenBaru->id,
            'kecamatans' => ['Kecamatan Baru 1', 'Kecamatan Baru 2']
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
            'nama_kecamatan' => 'Kecamatan Lama 1'
        ]);
        $this->assertDatabaseHas('kecamatans', [
            'uppt_id' => $uppt->id,
            'nama_kecamatan' => 'Kecamatan Baru 1'
        ]);
    }

    public function test_admin_can_delete_uppt()
    {
        $uppt = Uppt::create([
            'nama_uppt' => 'UPPT Hapus',
            'kabupaten_id' => $this->kabupaten->id
        ]);
        $uppt->kecamatans()->create(['nama_kecamatan' => 'Kec Hapus']);

        $response = $this->actingAs($this->admin)->delete("/uppt/{$uppt->id}");

        $response->assertRedirect(route('uppt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('uppts', [
            'id' => $uppt->id
        ]);
        // Asumsi cascading delete diatur di database, atau Eloquent boot method.
        // Jika tidak, test ini mungkin gagal. Tapi secara default kita uji uppt-nya hilang.
    }
}
