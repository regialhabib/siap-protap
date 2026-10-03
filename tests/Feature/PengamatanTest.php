<?php

namespace Tests\Feature;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengamatanTest extends TestCase
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
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kab Test']);
        return Uppt::create(['nama_uppt' => 'UPPT Test', 'kabupaten_id' => $kabupaten->id]);
    }

    public function test_forbids_admin_from_accessing_create_page(): void
    {
        $response = $this->actingAs($this->createAdmin())->get('/pengamatan/create');
        
        $response->assertForbidden();
    }

    public function test_renders_create_page_for_popt(): void
    {
        $response = $this->actingAs($this->createPopt($this->createUppt()))->get('/pengamatan/create');
        
        $response->assertOk();
    }

    public function test_rejects_creation_when_required_fields_are_missing(): void
    {
        $response = $this->actingAs($this->createPopt($this->createUppt()))->post('/pengamatan', []);

        $response->assertSessionHasErrors([
            'tanggal_pengamatan',
            'komoditas_id',
            'opt_id',
            'luas_komoditi_ha',
        ]);
    }

    public function test_creates_pengamatan_and_redirects(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi Test']);
        $opt = Opt::create(['nama_opt' => 'Wereng Test']);

        $payload = [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10.5,
            'serangan_ringan' => 2,
            'serangan_sedang' => 1,
            'serangan_berat' => 0.5,
            'kondisi_serangan' => 'Terkendali',
        ];

        $response = $this->actingAs($popt)->post('/pengamatan', $payload);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pengamatans', [
            'user_id' => $popt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10.5,
            'serangan_ringan' => 2,
            'serangan_sedang' => 1,
            'serangan_berat' => 0.5,
            'serangan_jumlah' => 3.5,
        ]);
    }

    public function test_rejects_creation_when_kecamatan_is_required_but_missing(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi Test']);
        $opt = Opt::create(['nama_opt' => 'Wereng Test']);
        Kecamatan::create(['nama_kecamatan' => 'Kecamatan Test', 'uppt_id' => $uppt->id]);

        $payload = [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10.5,
        ];

        $response = $this->actingAs($popt)->post('/pengamatan', $payload);
        
        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    public function test_creates_pengamatan_with_kecamatan_provided(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi Test']);
        $opt = Opt::create(['nama_opt' => 'Wereng Test']);
        $kec = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Test', 'uppt_id' => $uppt->id]);

        $payload = [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10.5,
            'kecamatan_id' => $kec->id,
        ];

        $response = $this->actingAs($popt)->post('/pengamatan', $payload);
        
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pengamatans', ['kecamatan_id' => $kec->id]);
    }
}
