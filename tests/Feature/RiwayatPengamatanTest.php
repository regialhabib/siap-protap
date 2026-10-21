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
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);
        $this->createObservation($admin, $uppt, $kecamatan, Komoditas::create(['nama_komoditas' => 'Karet']), $opt, $jenisOpt, '2026-02-05', 1, 0);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026]));

        $response->assertOk()
            ->assertSee('Riwayat Pengamatan Periode')
            ->assertSee('Serangan Bulan Ini')
            ->assertSee('Pengendalian Bulan Ini')
            ->assertSee('Total Periode')
            ->assertDontSee('Bulan Lalu')
            ->assertViewHas('data', fn ($data): bool => $data->total() === 1
                && count($data->first()->months) === 12
                && $data->first()->months[0]->bulan_label === 'Februari 2026'
                && $data->first()->months[11]->bulan_label === 'Januari 2027');
    }

    public function test_observation_history_is_empty_until_a_filter_is_applied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index'));

        $response->assertOk()
            ->assertSee('Pilih filter lalu tampilkan untuk membaca riwayat pengamatan.')
            ->assertViewHas('data', fn ($data): bool => $data->isEmpty() && $data->total() === 0);
    }

    public function test_popt_cannot_access_the_admin_observation_history(): void
    {
        $popt = User::factory()->create(['role' => 'popt']);

        $this->actingAs($popt)->get(route('pengamatan.riwayat.index'))->assertForbidden();
    }

    public function test_each_chain_lists_twelve_months_with_totals_and_blank_months(): void
    {
        $this->travelTo('2026-04-15');
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kelapa = Komoditas::create(['nama_komoditas' => 'Kelapa']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-01-15', 20, 10);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 0.5, 0.25);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-25', 1.5, 0.75, 1.25);
        $this->createObservation($admin, $uppt, $kecamatan, $kelapa, $opt, $jenisOpt, '2026-03-10', 4, 0);

        $filters = [
            'tahun' => 2026,
            'kabupaten_id' => $kabupaten->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
        ];
        $firstPage = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', $filters));
        $secondPage = $this->get(route('pengamatan.riwayat.index', [...$filters, 'page' => 2]));

        $data = $firstPage->viewData('data');
        $this->assertSame(2, $data->total());
        $this->assertCount(1, $data->getCollection());
        $this->assertCount(2, $firstPage->viewData('chainLabels'));
        $karet = $data->first();
        $this->assertSame('Karet', $karet->komoditas);

        $karetMonths = collect($karet->months)->keyBy('bulan_label');
        $this->assertSame(['tambah_jumlah' => 3.0, 'kendali_jumlah' => 1.25], array_intersect_key($karetMonths['Februari 2026']->values, array_flip(['tambah_jumlah', 'kendali_jumlah'])));
        $this->assertSame('1.75 Ha (Bertambah 1.75 Ha)', $karetMonths['Februari 2026']->kondisi_label);
        $this->assertFalse($karetMonths['Maret 2026']->ada_input);
        $this->assertSame('1.75 Ha (Tetap)', $karetMonths['April 2026']->kondisi_label);
        $this->assertTrue($karetMonths['Mei 2026']->is_blank);
        $this->assertSame(3.0, $karet->total['tambah_jumlah']);
        $this->assertSame(1.25, $karet->total['kendali_jumlah']);
        $this->assertSame(1.75, $karet->total['kondisi_akhir']);

        $kelapa = $secondPage->viewData('data')->first();
        $this->assertSame('Kelapa', $kelapa->komoditas);
        $this->assertTrue($kelapa->months[0]->is_blank);
        $this->assertSame('4 Ha (Bertambah 4 Ha)', $kelapa->months[1]->kondisi_label);
    }

    public function test_filters_by_opt_and_rejects_a_child_type_of_another_opt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $gugurDaun = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jamurAkar = Opt::create(['nama_opt' => 'Jamur Akar Putih']);
        $corynespora = JenisOpt::create(['opt_id' => $gugurDaun->id, 'nama_jenis_opt' => 'Corynespora']);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $gugurDaun, $corynespora, '2026-02-05', 1, 0);
        Pengamatan::create([
            'user_id' => $admin->id, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id, 'komoditas_id' => $karet->id,
            'opt_id' => $jamurAkar->id, 'tanggal_pengamatan' => '2026-02-10', 'luas_komoditi_ha' => 10, 'serangan_ringan' => 2, 'serangan_jumlah' => 2,
        ]);

        $filtered = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026, 'opt_id' => $jamurAkar->id]));

        $filtered->assertViewHas('data', fn ($data): bool => $data->total() === 1 && $data->first()->opt === 'Jamur Akar Putih');

        $this->get(route('pengamatan.riwayat.index', ['tahun' => 2026, 'opt_id' => $jamurAkar->id, 'jenis_opt_id' => $corynespora->id]))
            ->assertRedirect()
            ->assertSessionHasErrors(['jenis_opt_id' => 'Jenis OPT tidak termasuk OPT yang dipilih.']);
    }

    public function test_invalid_filter_shows_an_indonesian_validation_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026, 'opt_id' => 999]))
            ->assertRedirect()
            ->assertSessionHasErrors(['opt_id' => 'OPT yang dipilih tidak valid.']);
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
