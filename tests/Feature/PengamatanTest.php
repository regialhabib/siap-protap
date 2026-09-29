<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Uppt;

class PengamatanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat data master yang dibutuhkan untuk pengamatan
        $kabupaten = \App\Models\Kabupaten::create(['nama_kabupaten' => 'Kab Test']);
        $this->uppt = Uppt::create(['nama_uppt' => 'UPPT Test', 'kabupaten_id' => $kabupaten->id]);
        $this->komoditas = Komoditas::create(['nama_komoditas' => 'Padi Test']);
        $this->opt = Opt::create(['nama_opt' => 'Wereng Test']);

        // Buat user dengan role 'popt'
        $this->userPopt = User::factory()->create([
            'role' => 'popt',
            'uppt_id' => $this->uppt->id,
        ]);

        // Buat user dengan role 'admin'
        $this->userAdmin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_only_popt_can_access_create_page()
    {
        // Admin mencoba akses (harus gagal / 403)
        $responseAdmin = $this->actingAs($this->userAdmin)->get('/pengamatan/create');
        $responseAdmin->assertStatus(403);

        // POPT mencoba akses (harus sukses)
        $responsePopt = $this->actingAs($this->userPopt)->get('/pengamatan/create');
        $responsePopt->assertStatus(200);
    }

    public function test_validation_fails_when_required_fields_are_missing()
    {
        // Mengirim data kosong
        $response = $this->actingAs($this->userPopt)->post('/pengamatan', []);

        // Harus dikembalikan karena validasi gagal
        $response->assertSessionHasErrors([
            'tanggal_pengamatan',
            'komoditas_id',
            'opt_id',
            'luas_komoditi_ha'
        ]);
    }

    public function test_popt_can_store_pengamatan_successfully()
    {
        $payload = [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $this->komoditas->id,
            'opt_id' => $this->opt->id,
            'luas_komoditi_ha' => 10.5,
            'serangan_ringan' => 2,
            'serangan_sedang' => 1,
            'serangan_berat' => 0.5,
            'kondisi_serangan' => 'Terkendali',
        ];

        $response = $this->actingAs($this->userPopt)->post('/pengamatan', $payload);

        // Pastikan redirect ke dashboard dengan pesan sukses
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Pastikan data tersimpan di database dan dijumlahkan dengan benar
        $this->assertDatabaseHas('pengamatans', [
            'user_id' => $this->userPopt->id,
            'komoditas_id' => $this->komoditas->id,
            'opt_id' => $this->opt->id,
            'luas_komoditi_ha' => 10.5,
            'serangan_ringan' => 2,
            'serangan_sedang' => 1,
            'serangan_berat' => 0.5,
            'serangan_jumlah' => 3.5, // 2 + 1 + 0.5
        ]);
    }
}
