<?php

namespace Tests\Feature;

use App\Models\JenisOpt;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPengamatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_twelve_months_of_the_period_from_the_sidebar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026]));

        $response->assertOk()
            ->assertSee('Riwayat Pengamatan Periode')
            ->assertSee('Bulan Lalu')
            ->assertSee('Pengendalian Bulan Ini')
            ->assertSee('Riwayat Pengamatan')
            ->assertViewHas('data', fn ($data): bool => $data->count() === 12
                && $data->total() === 12
                && $data->first()->bulan_label === 'Februari 2026'
                && $data->last()->bulan_label === 'Januari 2027');
    }

    public function test_observation_history_is_empty_until_a_filter_is_applied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index'));

        $response->assertOk()
            ->assertViewHas('data', fn ($data): bool => $data->isEmpty() && $data->total() === 0)
            ->assertViewHas('recordCount', 0);
    }

    public function test_popt_cannot_access_the_admin_observation_history(): void
    {
        $popt = User::factory()->create(['role' => 'popt']);

        $this->actingAs($popt)->get(route('pengamatan.riwayat.index'))->assertForbidden();
    }

    public function test_monthly_rows_sum_entries_of_the_month_and_carry_the_chain_forward(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kelapa = Komoditas::create(['nama_komoditas' => 'Kelapa']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-01-15', 20, 10);
        $this->createObservation($admin, $uppt, $kecamatan, $kelapa, $opt, $jenisOpt, '2026-01-20', 4, 5);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 0.5, 0.25);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-25', 1.5, 0.75, 1.25);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [
            'tahun' => 2026,
            'kabupaten_id' => $kabupaten->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
        ]));

        $rows = $response->viewData('data')->getCollection()->where('is_empty', false)->keyBy('bulan_label');
        $this->assertCount(12, $rows);
        $this->assertSame('Karet', $rows['Februari 2026']->komoditas);
        $this->assertNull($rows['Februari 2026']->previous);
        $this->assertSame(
            ['tambah_jumlah' => 3.0, 'jumlah' => 3.0, 'pengendalian' => 1.25, 'kondisi_serangan' => 1.75, 'kondisi_label' => '1.75 Ha (Bertambah 1.75 Ha)'],
            array_intersect_key($rows['Februari 2026']->current, array_flip(['tambah_jumlah', 'jumlah', 'pengendalian', 'kondisi_serangan', 'kondisi_label']))
        );
        $this->assertFalse($rows['Maret 2026']->ada_input);
        $this->assertSame(['ringan' => 2.0, 'berat' => 1.0, 'jumlah' => 3.0, 'kondisi_serangan' => 1.75], $rows['Maret 2026']->previous);
        $this->assertSame(
            ['tambah_jumlah' => 0.0, 'jumlah' => 3.0, 'pengendalian' => 0.0, 'kondisi_serangan' => 1.75, 'kondisi_label' => '1.75 Ha (Tetap)'],
            array_intersect_key($rows['Maret 2026']->current, array_flip(['tambah_jumlah', 'jumlah', 'pengendalian', 'kondisi_serangan', 'kondisi_label']))
        );
    }

    public function test_filter_values_must_belong_to_the_selected_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupatenA = Kabupaten::create(['nama_kabupaten' => 'Kabupaten A']);
        $kabupatenB = Kabupaten::create(['nama_kabupaten' => 'Kabupaten B']);
        $upptB = Uppt::create(['nama_uppt' => 'UPPT B', 'kabupaten_id' => $kabupatenB->id]);

        $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [
            'kabupaten_id' => $kabupatenA->id,
            'uppt_id' => $upptB->id,
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('uppt_id');
    }

    private function createObservation(
        User $user,
        Uppt $uppt,
        Kecamatan $kecamatan,
        Komoditas $komoditas,
        Opt $opt,
        JenisOpt $jenisOpt,
        string $date,
        float $seranganRingan,
        float $seranganBerat,
        float $kendaliApbdKab = 0.0,
    ): Pengamatan {
        return Pengamatan::create([
            'user_id' => $user->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenisOpt->id,
            'tanggal_pengamatan' => $date,
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => $seranganRingan,
            'serangan_berat' => $seranganBerat,
            'serangan_jumlah' => $seranganRingan + $seranganBerat,
            'kendali_apbd_kab' => $kendaliApbdKab,
        ]);
    }
}
