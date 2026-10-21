<?php

namespace Tests\Feature;

use App\Models\Kabupaten;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
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
            'kecamatans' => [['nama_kecamatan' => 'Kecamatan Baru 1'], ['nama_kecamatan' => 'Kecamatan Baru 2']],
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

    public function test_renaming_uppt_keeps_retained_kecamatans_and_their_observations(): void
    {
        $kabupaten = $this->createKabupaten();
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Lama', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = $uppt->kecamatans()->create(['nama_kecamatan' => 'Kecamatan Tetap']);
        $pengamatan = Pengamatan::create([
            'user_id' => $this->createPopt()->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => Komoditas::create(['nama_komoditas' => 'Kakao'])->id,
            'opt_id' => Opt::create(['nama_opt' => 'Babi Hutan'])->id,
            'tanggal_pengamatan' => '2026-03-10',
            'luas_komoditi_ha' => 10,
        ]);

        $this->actingAs($this->createAdmin())->put("/uppt/{$uppt->id}", [
            'nama_uppt' => 'UPPT Baru',
            'kabupaten_id' => $kabupaten->id,
            'kecamatans' => [['id' => $kecamatan->id, 'nama_kecamatan' => 'Kecamatan Tetap (Ganti Nama)']],
        ])->assertRedirect(route('uppt.index'));

        $this->assertDatabaseHas('kecamatans', ['id' => $kecamatan->id, 'nama_kecamatan' => 'Kecamatan Tetap (Ganti Nama)']);
        $this->assertDatabaseHas('pengamatans', ['id' => $pengamatan->id, 'kecamatan_id' => $kecamatan->id]);
    }

    public function test_rejects_updating_a_kecamatan_of_another_uppt(): void
    {
        $kabupaten = $this->createKabupaten();
        $uppt = Uppt::create(['nama_uppt' => 'UPPT A', 'kabupaten_id' => $kabupaten->id]);
        $otherKecamatan = Uppt::create(['nama_uppt' => 'UPPT B', 'kabupaten_id' => $kabupaten->id])
            ->kecamatans()->create(['nama_kecamatan' => 'Kecamatan B']);

        $this->actingAs($this->createAdmin())->put("/uppt/{$uppt->id}", [
            'nama_uppt' => 'UPPT A',
            'kabupaten_id' => $kabupaten->id,
            'kecamatans' => [['id' => $otherKecamatan->id, 'nama_kecamatan' => 'Diambil Alih']],
        ])->assertSessionHasErrors('kecamatans.0.id');

        $this->assertDatabaseHas('kecamatans', ['id' => $otherKecamatan->id, 'nama_kecamatan' => 'Kecamatan B']);
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
