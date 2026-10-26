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
use App\Services\PengamatanHistoryService;
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
        $response->assertSee('Serangan Tambahan')->assertDontSee('Sedang');
    }

    public function test_create_page_lists_kecamatans_of_the_uppt_alphabetically(): void
    {
        $uppt = $this->createUppt();
        Kecamatan::create(['nama_kecamatan' => 'Rimbo Ulu', 'uppt_id' => $uppt->id]);
        Kecamatan::create(['nama_kecamatan' => 'Rimbo Bujang', 'uppt_id' => $uppt->id]);
        Kecamatan::create(['nama_kecamatan' => 'Kecamatan Lain', 'uppt_id' => $this->createUppt()->id]);

        $response = $this->actingAs($this->createPopt($uppt))->get('/pengamatan/create');

        $response->assertViewHas('kecamatans', fn ($kecamatans): bool => $kecamatans->pluck('nama_kecamatan')->all() === ['Rimbo Bujang', 'Rimbo Ulu']);
    }

    public function test_lists_rejected_fields_above_the_form(): void
    {
        $this->actingAs($this->createPopt($this->createUppt()))
            ->from('/pengamatan/create')
            ->followingRedirects()
            ->post('/pengamatan', [])
            ->assertSee('Data belum tersimpan. Periksa isian berikut:');
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
            'serangan_berat' => 0.5,
            'kendali_apbd_kab' => 0.25,
            'cara_pengendalian' => 'Pembersihan dan pemangkasan bagian terserang',
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
            'serangan_berat' => 0.5,
            'serangan_jumlah' => 2.5,
            'kendali_apbd_kab' => 0.25,
            'cara_pengendalian' => 'Pembersihan dan pemangkasan bagian terserang',
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function createObservation(User $user, Komoditas $komoditas, Opt $opt, string $date, array $attributes = []): Pengamatan
    {
        $ringan = $attributes['serangan_ringan'] ?? 0;
        $berat = $attributes['serangan_berat'] ?? 0;

        return Pengamatan::create([
            'user_id' => $user->id,
            'uppt_id' => $user->uppt_id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'tanggal_pengamatan' => $date,
            'luas_komoditi_ha' => 10,
            'serangan_jumlah' => $ringan + $berat,
            ...$attributes,
        ]);
    }

    public function test_history_continues_until_january_and_resets_every_february(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2025-11-10', ['serangan_ringan' => 2, 'serangan_berat' => 3]);
        $query = ['komoditas_id' => $komoditas->id, 'opt_id' => $opt->id];

        $january = $this->actingAs($popt)->getJson(route('pengamatan.history', $query + ['tanggal_pengamatan' => '2026-01-20']));
        $february = $this->getJson(route('pengamatan.history', $query + ['tanggal_pengamatan' => '2026-02-10']));

        $january->assertOk()
            ->assertJsonPath('summary.serangan_ringan', 2)
            ->assertJsonPath('summary.serangan_berat', 3)
            ->assertJsonPath('summary.kondisi_serangan', 5);
        $february->assertOk()
            ->assertJsonPath('summary.serangan_jumlah', 0)
            ->assertJsonPath('summary.luas_komoditi_ha', 0)
            ->assertJsonPath('summary.kondisi_serangan', 0);
    }

    public function test_history_separates_chains_by_child_opt(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenis = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Jenis Turunan']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 4]);

        $response = $this->actingAs($popt)->getJson(route('pengamatan.history', [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenis->id,
        ]));

        $response->assertOk()->assertJsonPath('summary.serangan_jumlah', 0);
    }

    public function test_history_for_an_edited_observation_excludes_itself_after_moving_its_date(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $edited = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 2]);
        $this->createObservation($popt, $komoditas, $opt, '2026-03-10', ['serangan_ringan' => 3]);

        $response = $this->actingAs($popt)->getJson(route('pengamatan.history', [
            'pengamatan_id' => $edited->id,
            'tanggal_pengamatan' => '2026-04-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
        ]));

        $response->assertOk()->assertJsonPath('summary.kondisi_serangan', 3);
    }

    public function test_history_lists_the_last_entry_and_entries_in_the_same_month_of_the_chain(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kelapa = Komoditas::create(['nama_komoditas' => 'Kelapa']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $karet, $opt, '2026-02-10', ['serangan_ringan' => 2]);
        $march = $this->createObservation($popt, $karet, $opt, '2026-03-05', ['serangan_ringan' => 1]);
        $this->createObservation($popt, $kelapa, $opt, '2026-03-07', ['serangan_ringan' => 4]);
        $query = ['tanggal_pengamatan' => '2026-03-20', 'komoditas_id' => $karet->id, 'opt_id' => $opt->id];

        $create = $this->actingAs($popt)->getJson(route('pengamatan.history', $query));
        $edit = $this->getJson(route('pengamatan.history', [...$query, 'pengamatan_id' => $march->id]));

        $create->assertOk()
            ->assertJsonPath('terakhir', '05 Maret 2026')
            ->assertJsonPath('bulan_sama', [['tanggal' => '05 Maret 2026', 'edit_url' => route('pengamatan.edit', $march)]]);
        $edit->assertOk()
            ->assertJsonPath('terakhir', '10 Februari 2026')
            ->assertJsonPath('bulan_sama', []);
    }

    public function test_calculates_condition_as_two_previous_plus_three_new_minus_two_control(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 2]);
        $this->createObservation($popt, $komoditas, $opt, '2026-03-10', ['serangan_ringan' => 3, 'kendali_apbd_kab' => 2]);

        $rows = Pengamatan::query()->orderBy('tanggal_pengamatan')->get();
        app(PengamatanHistoryService::class)->attachRunningTotals($rows);

        $this->assertSame(5.0, (float) $rows->last()->serangan_jumlah_berjalan);
        $this->assertSame(3.0, (float) $rows->last()->kondisi_serangan_ha);
    }

    public function test_rejects_control_larger_than_previous_condition_plus_new_attack(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 2]);
        $payload = [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
            'serangan_berat' => 1,
            'kendali_apbd_kab' => 3,
        ];

        $rejected = $this->actingAs($popt)->post('/pengamatan', [...$payload, 'kendali_masyarakat' => 0.5]);
        $rejected->assertSessionHasErrors(['pengendalian' => 'Total pengendalian melebihi luas serangan yang ada. Maksimal 3 Ha (kondisi sebelumnya + tambah serangan).']);
        $this->assertDatabaseCount('pengamatans', 1);

        $accepted = $this->post('/pengamatan', $payload);

        $accepted->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('pengamatans', 2);
        $this->assertDatabaseHas('pengamatans', ['kendali_apbd_kab' => 3, 'kendali_masyarakat' => 0, 'serangan_berat' => 1]);
    }

    public function test_allows_cumulative_attack_larger_than_commodity_area_after_control(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 8, 'kendali_apbd_kab' => 8]);

        $response = $this->actingAs($popt)->post('/pengamatan', [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
            'serangan_berat' => 7,
        ]);

        $response->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pengamatans', ['serangan_berat' => 7, 'serangan_jumlah' => 7]);
    }

    public function test_rejects_condition_larger_than_commodity_area(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 8]);

        $response = $this->actingAs($popt)->post('/pengamatan', [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
            'serangan_berat' => 7,
        ]);

        $response->assertSessionHasErrors(['kondisi' => 'Kondisi serangan (15 Ha) melebihi luas komoditi (10 Ha). Periksa kembali luas komoditi, serangan, atau pengendalian.']);
        $this->assertDatabaseCount('pengamatans', 1);
    }

    public function test_rejects_changes_that_push_a_later_condition_above_its_commodity_area(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 5]);
        $march = $this->createObservation($popt, $komoditas, $opt, '2026-03-15', ['kendali_apbd_kab' => 3]);
        $this->createObservation($popt, $komoditas, $opt, '2026-04-15', ['serangan_ringan' => 7]);

        $this->actingAs($popt)->put(route('pengamatan.update', $march), [
            'tanggal_pengamatan' => '2026-03-15',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
        ])->assertSessionHasErrors(['kondisi' => 'Perubahan ini membuat kondisi serangan pada pengamatan tanggal 15 April 2026 (12 Ha) melebihi luas komoditinya (10 Ha).']);

        $this->from(route('dashboard'))->delete(route('pengamatan.destroy', $march))
            ->assertSessionHas('error', 'Data tidak dapat dihapus karena kondisi serangan pada pengamatan tanggal 15 April 2026 akan melebihi luas komoditinya. Ubah data pada tanggal tersebut terlebih dahulu.');
        $this->assertSame(3.0, (float) $march->fresh()->kendali_apbd_kab);
    }

    public function test_rejects_zero_commodity_area_and_values_beyond_the_column_limit(): void
    {
        $popt = $this->createPopt($this->createUppt());

        $response = $this->actingAs($popt)->post('/pengamatan', [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => Komoditas::create(['nama_komoditas' => 'Karet'])->id,
            'opt_id' => Opt::create(['nama_opt' => 'Gugur Daun'])->id,
            'luas_komoditi_ha' => 0,
            'serangan_ringan' => 100000000,
        ]);

        $response->assertSessionHasErrors(['luas_komoditi_ha', 'serangan_ringan']);
        $this->assertDatabaseCount('pengamatans', 0);
    }

    public function test_popt_can_change_an_observation_only_within_two_days_of_input(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $pengamatan = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 2]);
        $historyUrl = route('pengamatan.history', [
            'tanggal_pengamatan' => '2026-02-20', 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id,
        ]);

        $this->travel(47)->hours();
        $this->actingAs($popt)->get(route('pengamatan.edit', $pengamatan))->assertOk();

        $this->travel(1)->hours();
        $this->get(route('pengamatan.edit', $pengamatan))->assertForbidden();
        $this->delete(route('pengamatan.destroy', $pengamatan))->assertForbidden();
        $this->getJson($historyUrl)->assertJsonPath('bulan_sama.0.edit_url', null);
        $this->get(route('dashboard'))->assertSee('Terkunci');
        $this->assertModelExists($pengamatan);
        $this->assertTrue($this->createAdmin()->can('update', $pengamatan));
    }

    public function test_rejects_update_that_makes_a_later_condition_negative(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $february = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 5]);
        $this->createObservation($popt, $komoditas, $opt, '2026-03-15', ['kendali_apbd_kab' => 4]);

        $response = $this->actingAs($popt)->put(route('pengamatan.update', $february), [
            'tanggal_pengamatan' => '2026-02-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 2,
        ]);

        $response->assertSessionHasErrors(['pengendalian' => 'Perubahan ini membuat kondisi serangan pada pengamatan tanggal 15 Maret 2026 menjadi minus (pengendalian maksimal di tanggal tersebut 2 Ha).']);
        $this->assertSame(5.0, (float) $february->fresh()->serangan_ringan);
    }

    public function test_rejects_deleting_an_observation_that_later_controls_depend_on(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $february = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 5]);
        $march = $this->createObservation($popt, $komoditas, $opt, '2026-03-15', ['kendali_apbd_kab' => 4]);

        $rejected = $this->actingAs($popt)->from(route('dashboard'))->delete(route('pengamatan.destroy', $february));
        $rejected->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Data tidak dapat dihapus karena kondisi serangan pada pengamatan tanggal 15 Maret 2026 akan menjadi minus. Ubah pengendalian pada tanggal tersebut terlebih dahulu.');
        $this->assertModelExists($february);

        $accepted = $this->delete(route('pengamatan.destroy', $march));

        $accepted->assertRedirect(route('dashboard'))->assertSessionHas('success');
        $this->assertModelMissing($march);
    }

    public function test_admin_edit_lists_kecamatans_of_the_observation_uppt(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Pengamatan', 'uppt_id' => $uppt->id]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $pengamatan = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['kecamatan_id' => $kecamatan->id]);

        $response = $this->actingAs($this->createAdmin())->get(route('pengamatan.edit', $pengamatan));

        $response->assertOk()->assertSee('Kecamatan Pengamatan');
    }

    public function test_admin_edit_shows_the_region_of_the_observation(): void
    {
        $popt = $this->createPopt($this->createUppt());
        $pengamatan = $this->createObservation($popt, Komoditas::create(['nama_komoditas' => 'Karet']), Opt::create(['nama_opt' => 'Gugur Daun']), '2026-02-10');

        $response = $this->actingAs($this->createAdmin())->get(route('pengamatan.edit', $pengamatan));

        $response->assertOk()->assertSee('Wilayah: UPPT Test');
    }

    public function test_rejects_update_with_kecamatan_from_another_uppt(): void
    {
        $uppt = $this->createUppt();
        $otherUppt = Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => $uppt->kabupaten_id]);
        $popt = $this->createPopt($uppt);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Pengamatan', 'uppt_id' => $uppt->id]);
        $otherKecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Lain', 'uppt_id' => $otherUppt->id]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $pengamatan = $this->createObservation($popt, $komoditas, $opt, '2026-02-10', ['kecamatan_id' => $kecamatan->id]);

        $response = $this->actingAs($this->createAdmin())->put(route('pengamatan.update', $pengamatan), [
            'tanggal_pengamatan' => '2026-02-10',
            'kecamatan_id' => $otherKecamatan->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
        ]);

        $response->assertSessionHasErrors(['kecamatan_id']);
        $this->assertSame($kecamatan->id, $pengamatan->fresh()->kecamatan_id);
    }

    public function test_forbids_updating_another_uppt_observation_before_validating_it(): void
    {
        $uppt = $this->createUppt();
        $otherPopt = $this->createPopt(Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => $uppt->kabupaten_id]));
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $pengamatan = $this->createObservation($this->createPopt($uppt), $komoditas, $opt, '2026-02-10', ['serangan_ringan' => 5]);
        $this->createObservation($this->createPopt($uppt), $komoditas, $opt, '2026-03-15', ['kendali_apbd_kab' => 4]);

        $response = $this->actingAs($otherPopt)->put(route('pengamatan.update', $pengamatan), [
            'tanggal_pengamatan' => '2026-02-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
        ]);

        $response->assertForbidden()->assertSessionHasNoErrors();
        $this->assertSame(5.0, (float) $pengamatan->fresh()->serangan_ringan);
    }

    public function test_rejects_values_with_more_than_two_decimal_places(): void
    {
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);

        $response = $this->actingAs($this->createPopt($this->createUppt()))->post('/pengamatan', [
            'tanggal_pengamatan' => '2026-03-10',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10.125,
            'serangan_ringan' => 1.005,
            'kendali_apbd_kab' => 0.5,
        ]);

        $response->assertSessionHasErrors(['luas_komoditi_ha', 'serangan_ringan'])->assertSessionDoesntHaveErrors('kendali_apbd_kab');
        $this->assertDatabaseCount('pengamatans', 0);
    }

    public function test_requires_and_saves_a_child_opt_when_parent_has_child_types(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenis = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora cassiicola']);
        $payload = [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
        ];

        $this->actingAs($popt)->post('/pengamatan', $payload)->assertSessionHasErrors(['jenis_opt_id']);
        $this->actingAs($popt)->post('/pengamatan', $payload + ['jenis_opt_id' => $jenis->id])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('pengamatans', ['opt_id' => $opt->id, 'jenis_opt_id' => $jenis->id]);
    }

    public function test_rejects_child_opt_that_belongs_to_another_parent(): void
    {
        $uppt = $this->createUppt();
        $popt = $this->createPopt($uppt);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $otherOpt = Opt::create(['nama_opt' => 'Jamur Akar']);
        $jenis = JenisOpt::create(['opt_id' => $otherOpt->id, 'nama_jenis_opt' => 'Jenis Lain']);

        $this->actingAs($popt)->post('/pengamatan', [
            'tanggal_pengamatan' => '2026-09-22',
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenis->id,
            'luas_komoditi_ha' => 10,
        ])->assertSessionHasErrors(['jenis_opt_id']);
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
