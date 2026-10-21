<?php

namespace Tests\Feature;

use App\Models\JenisOpt;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function createUppt()
    {
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);

        return Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
    }

    /** @param array<string, mixed> $attributes */
    private function createObservation(User $user, Uppt $uppt, Komoditas $komoditas, Opt $opt, string $date, array $attributes = []): Pengamatan
    {
        $ringan = $attributes['serangan_ringan'] ?? 0;
        $berat = $attributes['serangan_berat'] ?? 0;

        return Pengamatan::create([
            'user_id' => $user->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'tanggal_pengamatan' => $date,
            'luas_komoditi_ha' => 10,
            'serangan_jumlah' => $ringan + $berat,
            ...$attributes,
        ]);
    }

    public function test_renders_laporan_page_for_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $response = $this->actingAs($admin)->get('/laporan');

        $response->assertOk();
        $response->assertSee('Filter Laporan Bulanan')->assertSee('Pilih UPPT dan tampilkan laporan terlebih dahulu')->assertViewHas('uppt_id', '');
    }

    public function test_monthly_and_quarterly_reports_have_separate_pages_and_sidebar_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $monthlyResponse = $this->actingAs($admin)->get(route('laporan.index'));
        $monthlyResponse->assertSee('Filter Laporan Bulanan')
            ->assertSee('Laporan Bulanan')
            ->assertSee('Laporan Triwulan')
            ->assertSee('Laporan Tahunan')
            ->assertSee('aria-current="page"', false)
            ->assertDontSee('name="jenis"', false)
            ->assertDontSee('name="triwulan"', false);

        $this->get(route('laporan.triwulan'))
            ->assertSee('Filter Laporan Triwulan')
            ->assertSee('aria-current="page"', false)
            ->assertSee('name="triwulan"', false)
            ->assertSee('TW IV (November – Januari)')
            ->assertDontSee('name="bulan_awal"', false)
            ->assertDontSee('name="jenis"', false)
            ->assertDontSee('name="bulan"', false);
    }

    public function test_legacy_quarterly_url_redirects_to_the_separate_quarterly_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $this->actingAs($admin)->get('/laporan?jenis=triwulan&bulan_awal=2&bulan_tengah=3&bulan_akhir=4&tahun=2026')
            ->assertRedirect(route('laporan.triwulan', [
                'bulan_awal' => 2,
                'bulan_tengah' => 3,
                'bulan_akhir' => 4,
                'tahun' => 2026,
            ]));
    }

    public function test_report_shows_parent_and_child_opt_names(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenis = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora cassiicola']);
        Pengamatan::create([
            'user_id' => $admin->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenis->id,
            'tanggal_pengamatan' => now()->toDateString(),
            'luas_komoditi_ha' => 1,
            'serangan_jumlah' => 0,
        ]);

        $this->actingAs($admin)->get('/laporan?jenis=bulanan&bulan='.now()->month.'&tahun='.now()->year)->assertOk()->assertSee('Gugur Daun — Corynespora cassiicola');
    }

    public function test_forbids_popt_from_accessing_laporan_page()
    {
        $popt = User::factory()->create(['role' => 'popt']);

        $response = $this->actingAs($popt)->get('/laporan');

        $response->assertForbidden();
    }

    public function test_monthly_report_shows_this_month_values_and_carries_condition_forward(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-02-05', ['serangan_ringan' => 2, 'serangan_berat' => 3]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-02-20', ['serangan_ringan' => 2, 'kendali_apbd_kab' => 3]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-04-10', ['kendali_apbd_prov' => 1]);

        $february = $this->actingAs($admin)->get(route('laporan.index', ['bulan' => 2, 'tahun' => 2026, 'uppt_id' => $uppt->id]));
        $march = $this->get(route('laporan.index', ['bulan' => 3, 'tahun' => 2026, 'uppt_id' => $uppt->id]));
        $april = $this->get(route('laporan.index', ['bulan' => 4, 'tahun' => 2026, 'uppt_id' => $uppt->id]));

        $february->assertSee('4 Ha (Bertambah 4 Ha)')->assertDontSee('Sedang');
        $february->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->serangan_ringan === 4.0
            && $data->first()->serangan_berat === 3.0
            && $data->first()->serangan_jumlah === 7.0
            && $data->first()->kendali_apbd_kab === 3.0
            && $data->first()->kendali_jumlah === 3.0
            && $data->first()->kondisi_serangan_ha === 4.0);

        $march->assertSee('4 Ha (Tetap)')->assertSee('Tidak ada input bulan ini');
        $march->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->serangan_jumlah === 0.0
            && $data->first()->kendali_jumlah === 0.0
            && $data->first()->kondisi_serangan_ha === 4.0);

        $april->assertSee('3 Ha (Berkurang 1 Ha)');
        $april->assertViewHas('data', fn ($data): bool => $data->first()->kendali_apbd_prov === 1.0
            && $data->first()->kondisi_serangan_ha === 3.0);
    }

    public function test_monthly_report_hides_chains_without_input_whose_condition_is_zero(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $sawit = Komoditas::create(['nama_komoditas' => 'Sawit']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($admin, $uppt, $karet, $opt, '2026-03-10', ['serangan_ringan' => 3, 'kendali_apbd_kab' => 3]);
        $this->createObservation($admin, $uppt, $sawit, $opt, '2026-03-12', ['serangan_ringan' => 5, 'kendali_apbd_kab' => 2]);

        $march = $this->actingAs($admin)->get(route('laporan.index', ['bulan' => 3, 'tahun' => 2026, 'uppt_id' => $uppt->id]));
        $april = $this->get(route('laporan.index', ['bulan' => 4, 'tahun' => 2026, 'uppt_id' => $uppt->id]));

        $march->assertViewHas('data', fn ($data): bool => $data->map(fn ($row) => $row->komoditas->nama_komoditas)->all() === ['Karet', 'Sawit']);
        $april->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->komoditas->is($sawit)
            && $data->first()->kondisi_serangan_ha === 3.0);
    }

    public function test_monthly_report_starts_a_new_period_every_february(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2025-12-10', ['serangan_ringan' => 5]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-01-15', ['serangan_ringan' => 1]);

        $january = $this->actingAs($admin)->get(route('laporan.index', ['bulan' => 1, 'tahun' => 2026, 'uppt_id' => $uppt->id]));
        $february = $this->get(route('laporan.index', ['bulan' => 2, 'tahun' => 2026, 'uppt_id' => $uppt->id]));

        $january->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->serangan_jumlah === 1.0
            && $data->first()->kondisi_serangan_ha === 6.0);
        $february->assertViewHas('data', fn ($data): bool => $data->isEmpty());
    }

    public function test_monthly_report_searches_and_exports_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $padi = Komoditas::create(['nama_komoditas' => 'Padi']);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);
        $this->createObservation($admin, $uppt, $padi, $opt, '2026-01-15');
        $this->createObservation($admin, $uppt, $karet, $opt, '2026-01-20');

        $search = $this->actingAs($admin)->get(route('laporan.index', ['bulan' => 1, 'tahun' => 2026, 'search' => 'Padi']));
        $excel = $this->get(route('laporan.export.excel', ['bulan' => 1, 'tahun' => 2026, 'uppt_id' => $uppt->id]));
        $pdf = $this->get(route('laporan.export.pdf', ['bulan' => 1, 'tahun' => 2026, 'uppt_id' => $uppt->id]));

        $search->assertViewHas('data', fn ($data): bool => $data->count() === 1 && $data->first()->komoditas->is($padi));
        $excel->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=Laporan_Januari_2026.xlsx');
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_annual_report_sums_all_entries_of_the_period_across_kecamatans(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $kecamatanSatu = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Satu', 'uppt_id' => $uppt->id]);
        $kecamatanDua = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Dua', 'uppt_id' => $uppt->id]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-03-10', ['kecamatan_id' => $kecamatanSatu->id, 'luas_komoditi_ha' => 10, 'serangan_ringan' => 2, 'serangan_berat' => 1, 'kendali_apbd_prov' => 1, 'cara_pengendalian' => 'Pemangkasan']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-03-25', ['kecamatan_id' => $kecamatanSatu->id, 'luas_komoditi_ha' => 12, 'serangan_ringan' => 3, 'serangan_berat' => 2, 'kendali_apbd_prov' => 2, 'cara_pengendalian' => 'Penyemprotan']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2027-01-31', ['kecamatan_id' => $kecamatanSatu->id, 'luas_komoditi_ha' => 13, 'serangan_ringan' => 1, 'serangan_berat' => 1, 'kendali_apbd_kab' => 2]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-04-15', ['kecamatan_id' => $kecamatanDua->id, 'luas_komoditi_ha' => 20, 'serangan_ringan' => 4, 'serangan_berat' => 2, 'kendali_masyarakat' => 3, 'kendali_apbn' => 4, 'cara_pengendalian' => 'Sanitasi kebun']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-01-20', ['kecamatan_id' => $kecamatanDua->id, 'luas_komoditi_ha' => 99, 'serangan_ringan' => 9]);

        $response = $this->actingAs($admin)->get(route('laporan.tahunan', ['tahun' => 2026]));

        $response->assertSee('Laporan Tahunan 2026')->assertSee('Kerugian Hasil (Rp 000)')->assertSee('Februari 2026 – Januari 2027');
        $response->assertViewHas('data', function ($data): bool {
            $row = $data->first();

            return $data->count() === 1
                && $row->serangan_ringan === 10.0
                && $row->serangan_berat === 6.0
                && $row->serangan_jumlah === 16.0
                && $row->kendali_apbd_prov === 3.0
                && $row->kendali_apbd_kab === 2.0
                && $row->kendali_masyarakat === 3.0
                && $row->kendali_apbn === 4.0
                && $row->kendali_jumlah === 12.0
                && $row->luas_komoditi_ha === 33.0
                && $row->cara_pengendalian === 'Penyemprotan; Sanitasi kebun';
        });
    }

    public function test_annual_report_has_no_uppt_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $this->actingAs($admin)->get(route('laporan.tahunan'))
            ->assertOk()
            ->assertDontSee('name="uppt_id"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_annual_report_includes_all_commodities_and_opt_types_without_filter_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kelapa = Komoditas::create(['nama_komoditas' => 'Kelapa']);
        $gugurDaun = Opt::create(['nama_opt' => 'Gugur Daun']);
        $penggerekBatang = Opt::create(['nama_opt' => 'Penggerek Batang']);
        $jenisGugurDaun = JenisOpt::create(['opt_id' => $gugurDaun->id, 'nama_jenis_opt' => 'Corynespora']);
        $jenisPenggerekBatang = JenisOpt::create(['opt_id' => $penggerekBatang->id, 'nama_jenis_opt' => 'Penggerek']);

        Pengamatan::create([
            'user_id' => $admin->id, 'uppt_id' => $uppt->id, 'komoditas_id' => $karet->id,
            'opt_id' => $gugurDaun->id, 'jenis_opt_id' => $jenisGugurDaun->id,
            'tanggal_pengamatan' => '2026-05-10', 'luas_komoditi_ha' => 5, 'serangan_jumlah' => 2,
        ]);
        Pengamatan::create([
            'user_id' => $admin->id, 'uppt_id' => $uppt->id, 'komoditas_id' => $kelapa->id,
            'opt_id' => $penggerekBatang->id, 'jenis_opt_id' => $jenisPenggerekBatang->id,
            'tanggal_pengamatan' => '2026-05-10', 'luas_komoditi_ha' => 7, 'serangan_jumlah' => 3,
        ]);

        $this->actingAs($admin)->get(route('laporan.tahunan', [
            'tahun' => 2026,
            'komoditas_id' => $karet->id,
            'jenis_opt_id' => $jenisGugurDaun->id,
        ]))
            ->assertOk()
            ->assertDontSee('name="komoditas_id"', false)
            ->assertDontSee('name="jenis_opt_id"', false)
            ->assertViewHas('data', fn ($data): bool => $data->count() === 2);
    }

    public function test_annual_report_groups_uppts_of_one_kabupaten_and_exports_annual_layout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $otherUppt = Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => 1]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $attributes = ['user_id' => $admin->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id, 'tanggal_pengamatan' => '2026-05-10', 'luas_komoditi_ha' => 5, 'serangan_jumlah' => 2];
        Pengamatan::create($attributes + ['uppt_id' => $uppt->id]);
        Pengamatan::create($attributes + ['uppt_id' => $otherUppt->id]);
        Pengamatan::create(array_merge($attributes, ['uppt_id' => $uppt->id, 'tanggal_pengamatan' => '2025-12-10']));

        $this->actingAs($admin)->get(route('laporan.tahunan', ['tahun' => 2026]))
            ->assertViewHas('data', fn ($data): bool => $data->count() === 1 && $data->first()->luas_komoditi_ha === 10.0);

        $this->get(route('laporan.export.tahunan.excel', ['tahun' => 2026]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=Laporan_Tahunan_2026.xlsx');

        $this->get(route('laporan.export.tahunan.pdf', ['tahun' => 2026]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_monthly_exports_require_a_selected_uppt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('laporan.export.excel', ['jenis' => 'bulanan', 'bulan' => 1, 'tahun' => 2026]))
            ->assertRedirect()
            ->assertSessionHasErrors('uppt_id');

        $this->get(route('laporan.export.pdf', ['bulan' => 1, 'tahun' => 2026]))
            ->assertRedirect()
            ->assertSessionHasErrors('uppt_id');
    }

    public function test_monthly_and_annual_reports_paginate_five_rows_per_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);

        foreach (range(1, 6) as $number) {
            $opt = Opt::create(['nama_opt' => "OPT {$number}"]);
            Pengamatan::create([
                'user_id' => $admin->id,
                'uppt_id' => $uppt->id,
                'komoditas_id' => $komoditas->id,
                'opt_id' => $opt->id,
                'tanggal_pengamatan' => '2026-05-10',
                'luas_komoditi_ha' => 5,
                'serangan_jumlah' => 1,
            ]);
        }

        $this->actingAs($admin)->get(route('laporan.index', ['jenis' => 'bulanan', 'bulan' => 5, 'tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertViewHas('data', fn ($data): bool => $data->total() === 6 && $data->count() === 5)
            ->assertSee('page=2');

        $this->get(route('laporan.index', ['jenis' => 'bulanan', 'bulan' => 5, 'tahun' => 2026, 'uppt_id' => $uppt->id, 'page' => 2]))
            ->assertViewHas('data', fn ($data): bool => $data->total() === 6 && $data->count() === 1);

        $this->get(route('laporan.tahunan', ['tahun' => 2026, 'page' => 2]))
            ->assertViewHas('data', fn ($data): bool => $data->total() === 6 && $data->count() === 1);
    }

    public function test_returns_kecamatans_for_uppt()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt1 = $this->createUppt();
        $kec = Kecamatan::create(['nama_kecamatan' => 'Kec Test', 'uppt_id' => $uppt1->id]);

        $response = $this->actingAs($admin)->getJson("/api/uppt/{$uppt1->id}/kecamatans");

        $response->assertOk();
        $response->assertJsonFragment(['nama_kecamatan' => 'Kec Test']);
    }

    public function test_filters_laporan_by_kecamatan()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt1 = $this->createUppt();
        $kec1 = Kecamatan::create(['nama_kecamatan' => 'Kec 1', 'uppt_id' => $uppt1->id]);
        $kec2 = Kecamatan::create(['nama_kecamatan' => 'Kec 2', 'uppt_id' => $uppt1->id]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);
        $this->createObservation($admin, $uppt1, $komoditas, $opt, '2026-01-15', ['kecamatan_id' => $kec1->id]);
        $this->createObservation($admin, $uppt1, $komoditas, $opt, '2026-01-15', ['kecamatan_id' => $kec2->id]);

        $res = $this->actingAs($admin)->get("/laporan?jenis=bulanan&bulan=1&tahun=2026&uppt_id={$uppt1->id}&kecamatan_id={$kec1->id}");

        $res->assertViewHas('data', fn ($data): bool => $data->count() === 1 && $data->first()->kecamatan->is($kec1));
    }

    public function test_quarterly_report_sums_three_months_per_kabupaten_and_compares_with_previous_quarter(): void
    {
        $this->travelTo('2026-08-15');
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $upptDua = Uppt::create(['nama_uppt' => 'UPPT Dua', 'kabupaten_id' => 1]);
        Uppt::create(['nama_uppt' => 'UPPT Tiga', 'kabupaten_id' => 1]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        foreach ([
            ['2026-02-10', 4, 2], ['2026-03-10', 1, 1], ['2026-05-10', 2, 3], ['2026-06-10', 1, 0], ['2026-07-10', 0, 1],
        ] as [$date, $serangan, $kendali]) {
            $this->createObservation($admin, $uppt, $komoditas, $opt, $date, ['serangan_ringan' => $serangan, 'kendali_apbd_kab' => $kendali]);
        }
        $this->createObservation($admin, $upptDua, $komoditas, $opt, '2026-05-20', ['serangan_berat' => 3]);

        $firstQuarter = $this->actingAs($admin)->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 1]));
        $secondQuarter = $this->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 2]));

        $firstQuarter->assertSee('2 Ha (Bertambah 2 Ha)');
        $firstQuarter->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->serangan_jumlah === 5.0
            && $data->first()->kendali_jumlah === 3.0);
        $secondQuarter->assertSee('Laporan Triwulan II Tahun 2026')->assertSee('4 Ha (Bertambah 2 Ha)');
        $secondQuarter->assertViewHas('data', fn ($data): bool => $data->count() === 1
            && $data->first()->kabupaten === 'Test Kab'
            && $data->first()->serangan_ringan === 3.0
            && $data->first()->serangan_berat === 3.0
            && $data->first()->serangan_jumlah === 6.0
            && $data->first()->kendali_apbd_kab === 4.0
            && $data->first()->kondisi_serangan_ha === 4.0);
        $secondQuarter->assertViewHas('belumLapor', fn ($belumLapor): bool => $belumLapor['Mei 2026']->all() === ['UPPT Tiga']
            && $belumLapor['Juli 2026']->all() === ['UPPT Dua', 'UPPT Tiga']);
    }

    public function test_quarterly_report_hides_chains_without_input_whose_condition_is_zero(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $sawit = Komoditas::create(['nama_komoditas' => 'Sawit']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($admin, $uppt, $karet, $opt, '2026-03-10', ['serangan_ringan' => 3, 'kendali_apbd_kab' => 3]);
        $this->createObservation($admin, $uppt, $sawit, $opt, '2026-03-12', ['serangan_ringan' => 5, 'kendali_apbd_kab' => 2]);

        $firstQuarter = $this->actingAs($admin)->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 1]));
        $secondQuarter = $this->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 2]));

        $firstQuarter->assertViewHas('data', fn ($data): bool => $data->pluck('komoditas')->all() === ['Karet', 'Sawit']);
        $secondQuarter->assertViewHas('data', fn ($data): bool => $data->pluck('komoditas')->all() === ['Sawit']
            && $data->first()->kondisi_serangan_ha === 3.0);
    }

    public function test_fourth_quarter_includes_january_of_the_next_year_and_exports_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2026-11-10', ['serangan_ringan' => 1]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2027-01-10', ['serangan_ringan' => 2]);
        $this->createObservation($admin, $uppt, $komoditas, $opt, '2027-02-10', ['serangan_ringan' => 7]);

        $response = $this->actingAs($admin)->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 4]));
        $excel = $this->get(route('laporan.export.triwulan.excel', ['tahun' => 2026, 'triwulan' => 4]));
        $pdf = $this->get(route('laporan.export.triwulan.pdf', ['tahun' => 2026, 'triwulan' => 4]));

        $response->assertSee('November 2026 – Januari 2027');
        $response->assertViewHas('data', fn ($data): bool => $data->count() === 1 && $data->first()->serangan_jumlah === 3.0);
        $excel->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=Laporan_Triwulan_IV_2026.xlsx');
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_quarterly_report_rejects_an_unknown_quarter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('laporan.triwulan', ['tahun' => 2026, 'triwulan' => 5]))
            ->assertRedirect()
            ->assertSessionHasErrors('triwulan');
    }
}
