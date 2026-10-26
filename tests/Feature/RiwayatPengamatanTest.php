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
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 1, 0);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [
            'tahun' => 2026, 'kabupaten_id' => $kabupaten->id, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => $karet->id, 'opt_id' => $opt->id, 'jenis_opt_id' => $jenisOpt->id,
        ]));

        $response->assertOk()
            ->assertSee('Riwayat Pengamatan Periode')
            ->assertSee('Kondisi Awal')
            ->assertSee('= Kondisi Akhir')
            ->assertSee('Total Periode')
            ->assertDontSee('Bulan Lalu')
            ->assertViewHas('chain', fn ($chain): bool => count($chain->months) === 12
                && [$chain->komoditas, $chain->opt, $chain->jenis_opt] === ['Karet', 'Gugur Daun', 'Corynespora']
                && $chain->months[0]->bulan_label === 'Februari 2026'
                && $chain->months[11]->bulan_label === 'Januari 2027');
    }

    public function test_admin_exports_the_selected_chain_and_popt_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 1, 0);
        $filters = [
            'tahun' => 2026, 'kabupaten_id' => $kabupaten->id, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => $karet->id, 'opt_id' => $opt->id, 'jenis_opt_id' => $jenisOpt->id,
        ];

        $this->actingAs($admin)->get(route('pengamatan.riwayat.index', $filters))
            ->assertSee(route('pengamatan.riwayat.export.pdf', $filters));
        $this->get(route('pengamatan.riwayat.export.excel', $filters))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=Riwayat_Pengamatan_uppt_uji_karet_gugur_daun_corynespora_2026.xlsx');
        $this->get(route('pengamatan.riwayat.export.pdf', $filters))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('pengamatan.riwayat.export.pdf', [...$filters, 'tahun' => 2025]))->assertNotFound();

        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);
        $this->actingAs($popt)->get(route('pengamatan.riwayat.index', $filters))->assertDontSee('Unduh Excel');
        $this->get(route('pengamatan.riwayat.export.excel', $filters))->assertForbidden();
    }

    public function test_observation_history_is_empty_until_a_filter_is_applied(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index'));

        $response->assertOk()
            ->assertSee('Isi semua filter lalu klik Tampilkan untuk membaca riwayat pengamatan.')
            ->assertSessionHasNoErrors()
            ->assertViewHas('chain', null);
    }

    public function test_popt_without_an_uppt_cannot_open_the_observation_history(): void
    {
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => null]);

        $this->actingAs($popt)->get(route('pengamatan.riwayat.index'))->assertForbidden();
    }

    public function test_popt_reads_only_the_chains_of_their_own_uppt(): void
    {
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $ownUppt = Uppt::create(['nama_uppt' => 'UPPT Sendiri', 'kabupaten_id' => $kabupaten->id]);
        $otherUppt = Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => Kabupaten::create(['nama_kabupaten' => 'Kabupaten Lain'])->id]);
        $ownKecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Sendiri', 'uppt_id' => $ownUppt->id]);
        $otherKecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Lain', 'uppt_id' => $otherUppt->id]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $ownUppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);
        $this->createObservation($popt, $ownUppt, $ownKecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 2, 0);
        $this->createObservation($popt, $otherUppt, $otherKecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 7, 0);
        $chain = ['tahun' => 2026, 'komoditas_id' => $karet->id, 'opt_id' => $opt->id, 'jenis_opt_id' => $jenisOpt->id];

        $this->actingAs($popt)->get(route('pengamatan.riwayat.index'))
            ->assertOk()
            ->assertSee('UPPT Sendiri · Kabupaten Uji')
            ->assertDontSee('-- Pilih UPPT --')
            ->assertViewHas('chain', null);

        $this->get(route('pengamatan.riwayat.index', [...$chain, 'kecamatan_id' => $ownKecamatan->id]))
            ->assertViewHas('chain', fn ($chain): bool => $chain->uppt === 'UPPT Sendiri' && $chain->total['tambah_jumlah'] === 2.0);

        $this->get(route('pengamatan.riwayat.index', [...$chain, 'kabupaten_id' => $otherUppt->kabupaten_id, 'uppt_id' => $otherUppt->id, 'kecamatan_id' => $otherKecamatan->id]))
            ->assertRedirect()
            ->assertSessionHasErrors(['kecamatan_id' => 'Kecamatan tidak berada di wilayah yang dipilih.']);
    }

    public function test_a_chain_lists_twelve_months_with_totals_and_blank_months(): void
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
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenisOpt->id,
        ];
        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [...$filters, 'komoditas_id' => $karet->id]));
        $response->assertSee('Februari 2026')->assertDontSee('Maret 2026')->assertDontSee('belum ada data');
        $karet = $response->viewData('chain');
        $this->assertSame('Karet', $karet->komoditas);

        $karetMonths = collect($karet->months)->keyBy('bulan_label');
        $this->assertSame(['tambah_jumlah' => 3.0, 'kendali_jumlah' => 1.25], array_intersect_key($karetMonths['Februari 2026']->values, array_flip(['tambah_jumlah', 'kendali_jumlah'])));
        $this->assertSame('1,75 Ha (Bertambah 1,75 Ha)', $karetMonths['Februari 2026']->kondisi_label);
        $this->assertSame([0.0, 1.75], [$karetMonths['Februari 2026']->kondisi_awal, $karetMonths['Februari 2026']->kondisi_akhir]);
        $this->assertSame([1.75, 1.75], [$karetMonths['Maret 2026']->kondisi_awal, $karetMonths['Maret 2026']->kondisi_akhir]);
        $this->assertNull($karetMonths['Mei 2026']->kondisi_awal);
        $this->assertFalse($karetMonths['Maret 2026']->ada_input);
        $this->assertSame('1,75 Ha (Tetap)', $karetMonths['April 2026']->kondisi_label);
        $this->assertTrue($karetMonths['Mei 2026']->is_blank);
        $this->assertSame(3.0, $karet->total['tambah_jumlah']);
        $this->assertSame(1.25, $karet->total['kendali_jumlah']);
        $this->assertSame(1.75, $karet->total['kondisi_akhir']);

        $kelapa = $this->get(route('pengamatan.riwayat.index', [...$filters, 'komoditas_id' => $kelapa->id]))->viewData('chain');
        $this->assertSame('Kelapa', $kelapa->komoditas);
        $this->assertTrue($kelapa->months[0]->is_blank);
        $this->assertSame('4 Ha (Bertambah 4 Ha)', $kelapa->months[1]->kondisi_label);
    }

    public function test_every_filter_is_required_to_pick_exactly_one_chain(): void
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

        $location = ['tahun' => 2026, 'kabupaten_id' => $kabupaten->id, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id, 'komoditas_id' => $karet->id];

        $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026, 'opt_id' => $jamurAkar->id]))
            ->assertRedirect()
            ->assertSessionHasErrors(['kabupaten_id', 'uppt_id', 'komoditas_id']);

        $this->get(route('pengamatan.riwayat.index', [...$location, 'opt_id' => $jamurAkar->id]))
            ->assertViewHas('chain', fn ($chain): bool => $chain->opt === 'Jamur Akar Putih');

        $this->get(route('pengamatan.riwayat.index', [...$location, 'opt_id' => $gugurDaun->id]))
            ->assertRedirect()
            ->assertSessionHasErrors(['jenis_opt_id' => 'Jenis OPT wajib diisi.']);

        $this->get(route('pengamatan.riwayat.index', [...$location, 'opt_id' => $jamurAkar->id, 'jenis_opt_id' => $corynespora->id]))
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
            'tahun' => 2026,
            'kabupaten_id' => $kabupatenA->id,
            'uppt_id' => $upptB->id,
            'komoditas_id' => Komoditas::create(['nama_komoditas' => 'Karet'])->id,
            'opt_id' => Opt::create(['nama_opt' => 'Jamur Akar Putih'])->id,
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
