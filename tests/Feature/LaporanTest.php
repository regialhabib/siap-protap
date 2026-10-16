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
            ->assertDontSee('name="bulan_awal"', false);

        $this->get(route('laporan.triwulan'))
            ->assertSee('Filter Laporan Triwulan')
            ->assertSee('aria-current="page"', false)
            ->assertSee('name="bulan_awal"', false)
            ->assertSee('name="bulan_tengah"', false)
            ->assertSee('name="bulan_akhir"', false)
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

    public function test_annual_report_accumulates_monthly_latest_rows_across_kecamatans(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $kecamatanSatu = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Satu', 'uppt_id' => $uppt->id]);
        $kecamatanDua = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Dua', 'uppt_id' => $uppt->id]);
        $common = ['user_id' => $admin->id, 'uppt_id' => $uppt->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id];

        Pengamatan::create($common + ['kecamatan_id' => $kecamatanSatu->id, 'tanggal_pengamatan' => '2026-01-10', 'luas_komoditi_ha' => 10, 'serangan_ringan' => 2, 'serangan_berat' => 1, 'serangan_jumlah' => 3, 'kendali_apbd_prov' => 1, 'cara_pengendalian' => 'Pemangkasan']);
        Pengamatan::create($common + ['kecamatan_id' => $kecamatanSatu->id, 'tanggal_pengamatan' => '2026-01-25', 'luas_komoditi_ha' => 12, 'serangan_ringan' => 3, 'serangan_berat' => 2, 'serangan_jumlah' => 5, 'kendali_apbd_prov' => 2, 'cara_pengendalian' => 'Penyemprotan']);
        Pengamatan::create($common + ['kecamatan_id' => $kecamatanSatu->id, 'tanggal_pengamatan' => '2026-02-10', 'luas_komoditi_ha' => 13, 'serangan_ringan' => 1, 'serangan_berat' => 1, 'serangan_jumlah' => 2, 'kendali_apbd_kab' => 2, 'cara_pengendalian' => 'Penyemprotan']);
        Pengamatan::create($common + ['kecamatan_id' => $kecamatanDua->id, 'tanggal_pengamatan' => '2026-03-15', 'luas_komoditi_ha' => 20, 'serangan_ringan' => 4, 'serangan_berat' => 2, 'serangan_jumlah' => 6, 'kendali_masyarakat' => 3, 'kendali_apbn' => 4, 'cara_pengendalian' => 'Sanitasi kebun']);

        $this->actingAs($admin)->get(route('laporan.tahunan', ['tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertOk()->assertSee('Laporan Tahunan 2026')->assertSee('Kerugian Hasil (Rp 000)')
            ->assertViewHas('data', function ($data): bool {
                $row = $data->first();

                return $data->count() === 1
                    && (float) $row->serangan_ringan === 8.0
                    && (float) $row->serangan_berat === 5.0
                    && (float) $row->serangan_jumlah === 13.0
                    && (float) $row->kendali_apbd_prov === 2.0
                    && (float) $row->kendali_apbd_kab === 2.0
                    && (float) $row->kendali_masyarakat === 3.0
                    && (float) $row->kendali_apbn === 4.0
                    && (float) $row->kendali_jumlah === 11.0
                    && (float) $row->luas_komoditi_ha === 33.0
                    && $row->cara_pengendalian === 'Penyemprotan; Sanitasi kebun'
                    && $row->kerugian_hasil === '';
            });
    }

    public function test_annual_report_does_not_preselect_an_uppt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $this->actingAs($admin)->get(route('laporan.tahunan'))
            ->assertOk()
            ->assertViewHas('upptId', '')
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

    public function test_annual_report_filters_by_uppt_and_exports_annual_layout(): void
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

        $this->actingAs($admin)->get(route('laporan.tahunan', ['tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertViewHas('data', fn ($data): bool => $data->count() === 1);

        $this->get(route('laporan.export.tahunan.excel', ['tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('content-disposition', 'attachment; filename=Laporan_Tahunan_2026.xlsx');

        $this->get(route('laporan.export.tahunan.pdf', ['tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_monthly_and_quarterly_exports_require_a_selected_uppt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('laporan.export.excel', ['jenis' => 'bulanan', 'bulan' => 1, 'tahun' => 2026]))
            ->assertRedirect()
            ->assertSessionHasErrors('uppt_id');

        $this->get(route('laporan.export.triwulan.pdf', ['bulan_awal' => 1, 'bulan_tengah' => 2, 'bulan_akhir' => 3, 'tahun' => 2026]))
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

        $this->get(route('laporan.tahunan', ['tahun' => 2026, 'uppt_id' => $uppt->id, 'page' => 2]))
            ->assertViewHas('data', fn ($data): bool => $data->total() === 6 && $data->count() === 1);
    }

    public function test_filters_and_exports_laporan_by_time()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt1 = $this->createUppt();
        $uppt2 = Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => 1]);

        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);

        $commonData = [
            'user_id' => $admin->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10, 'serangan_ringan' => 0, 'serangan_berat' => 0,
            'serangan_jumlah' => 0,
        ];

        // Data 1: UPPT 1, Jan 2026.
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2026-01-15']));

        foreach (['2026-02-15', '2026-03-15', '2026-04-15'] as $date) {
            Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => $date]));
        }

        // Data 2: UPPT 2, Jun 2026 (bulan terakhir Triwulan 2)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt2->id, 'tanggal_pengamatan' => '2026-06-10']));

        // Data 3: UPPT 1, Des 2025 (Triwulan 4, 2025)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2025-12-20']));

        // 1. Uji Filter Bulanan (Januari 2026) & UPPT 1
        $resBulan = $this->actingAs($admin)->get("/laporan?jenis=bulanan&bulan=1&tahun=2026&uppt_id={$uppt1->id}");
        $resBulan->assertViewHas('data', function ($data) {
            return $data->count() === 1 && $data->first()->tanggal_pengamatan->format('Y-m-d') === '2026-01-15';
        });

        // 2. Rentang bergeser Februari-April mengambil snapshot bulan April.
        $resTriwulan = $this->actingAs($admin)->get(route('laporan.triwulan', ['bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4, 'tahun' => 2026, 'uppt_id' => $uppt1->id]));
        $resTriwulan->assertViewHas('data', function ($data) {
            return $data->count() === 1 && $data->first()->tanggal_pengamatan->format('Y-m-d') === '2026-04-15';
        });

        // 3. Legacy quarterly URLs move to the dedicated quarterly page.
        $this->actingAs($admin)->get('/laporan?jenis=triwulan&bulan_awal=2&bulan_tengah=3&bulan_akhir=4&tahun=2026&uppt_id='.$uppt1->id)
            ->assertRedirect(route('laporan.triwulan', ['bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4, 'tahun' => 2026, 'uppt_id' => $uppt1->id]));

        // 4. Uji pencarian pada laporan bulanan.
        $resSearch = $this->actingAs($admin)->get('/laporan?jenis=bulanan&bulan=1&tahun=2026&search=Padi&uppt_id=');
        $resSearch->assertViewHas('data', function ($data) {
            return $data->count() === 1;
        });

        // 5. Uji export Excel membawa rentang Februari-April.
        $resExcel = $this->actingAs($admin)->get(route('laporan.export.triwulan.excel', ['bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4, 'tahun' => 2026, 'uppt_id' => $uppt1->id]));
        $resExcel->assertOk();
        $resExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $resExcel->assertHeader('content-disposition', 'attachment; filename=Laporan_Triwulan_02-04_2026.xlsx');

        $resQuarterlyPdf = $this->get(route('laporan.export.triwulan.pdf', ['bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4, 'tahun' => 2026, 'uppt_id' => $uppt1->id]));
        $resQuarterlyPdf->assertOk()->assertHeader('content-type', 'application/pdf');

        // 6. Uji Export PDF (Filter Bulanan Des 2025, UPPT 1)
        $resPdf = $this->actingAs($admin)->get("/laporan/export/pdf?jenis=bulanan&bulan=12&tahun=2025&uppt_id={$uppt1->id}");
        $resPdf->assertOk();
        $resPdf->assertHeader('content-type', 'application/pdf');
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

        Pengamatan::create([
            'user_id' => $admin->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10, 'serangan_jumlah' => 0,
            'uppt_id' => $uppt1->id, 'kecamatan_id' => $kec1->id, 'tanggal_pengamatan' => '2026-01-15',
        ]);

        Pengamatan::create([
            'user_id' => $admin->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10, 'serangan_jumlah' => 0,
            'uppt_id' => $uppt1->id, 'kecamatan_id' => $kec2->id, 'tanggal_pengamatan' => '2026-01-15',
        ]);

        $res = $this->actingAs($admin)->get("/laporan?jenis=bulanan&bulan=1&tahun=2026&uppt_id={$uppt1->id}&kecamatan_id={$kec1->id}");
        $res->assertViewHas('data', function ($data) use ($kec1) {
            return $data->count() === 1 && $data->first()->kecamatan_id === $kec1->id;
        });
    }

    public function test_monthly_report_uses_latest_record_attack_and_running_condition(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $common = [
            'user_id' => $admin->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10,
        ];
        Pengamatan::create($common + [
            'tanggal_pengamatan' => '2026-02-05',
            'serangan_ringan' => 2,
            'serangan_berat' => 3,
            'serangan_jumlah' => 5,
        ]);
        Pengamatan::create($common + [
            'tanggal_pengamatan' => '2026-02-20',
            'serangan_ringan' => 2,
            'serangan_jumlah' => 2,
            'kendali_apbd_kab' => 3,
        ]);

        $response = $this->actingAs($admin)->get('/laporan?jenis=bulanan&bulan=2&tahun=2026&uppt_id=');
        $response->assertViewHas('data', function ($data): bool {
            return $data->count() === 1
                && (float) $data->first()->serangan_jumlah === 2.0
                && (float) $data->first()->serangan_jumlah_berjalan === 7.0
                && (float) $data->first()->kendali_jumlah === 3.0
                && (float) $data->first()->kondisi_serangan_ha === 4.0;
        })->assertSee('JML')->assertDontSee('Sedang');
    }

    public function test_quarterly_report_requires_data_in_all_selected_months_for_uppt_and_kecamatan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        Pengamatan::create([
            'user_id' => $admin->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'tanggal_pengamatan' => '2026-02-15',
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 2,
        ]);

        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);

        $this->actingAs($admin)->get(route('laporan.triwulan', [
            'bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4,
            'tahun' => 2026, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
        ]))
            ->assertSee('Laporan belum tersedia karena data tiga bulan belum lengkap.')
            ->assertViewHas('data', fn ($data): bool => $data->isEmpty())
            ->assertViewHas('reportAvailable', false);

        $this->get(route('laporan.export.triwulan.pdf', [
            'bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4,
            'tahun' => 2026, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
        ]))
            ->assertRedirect(route('laporan.triwulan', [
                'bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4,
                'tahun' => 2026, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
            ]))
            ->assertSessionHasErrors('bulan_awal');

        $this->get(route('laporan.export.triwulan.excel', [
            'bulan_awal' => 2, 'bulan_tengah' => 3, 'bulan_akhir' => 4,
            'tahun' => 2026, 'uppt_id' => $uppt->id, 'kecamatan_id' => $kecamatan->id,
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('bulan_awal');
    }

    public function test_quarterly_months_must_be_consecutive_in_the_same_year(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt = $this->createUppt();

        $this->actingAs($admin)->get(route('laporan.triwulan', [
            'bulan_awal' => 2, 'bulan_tengah' => 4, 'bulan_akhir' => 5, 'tahun' => 2026,
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('bulan_tengah');

        $this->get(route('laporan.export.triwulan.excel', [
            'bulan_awal' => 11, 'bulan_tengah' => 12, 'bulan_akhir' => 1,
            'tahun' => 2026, 'uppt_id' => $uppt->id,
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('bulan_tengah');
    }
}
