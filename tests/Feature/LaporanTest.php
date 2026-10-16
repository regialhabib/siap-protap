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
        $response->assertSee('Export Laporan')->assertViewHas('uppt_id', '');
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

    public function test_annual_history_shows_observations_from_oldest_to_newest_with_running_condition(): void
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
            'tanggal_pengamatan' => '2026-01-10',
            'serangan_ringan' => 2,
            'serangan_jumlah' => 2,
        ]);
        Pengamatan::create($common + [
            'tanggal_pengamatan' => '2026-02-10',
            'serangan_ringan' => 3,
            'serangan_jumlah' => 3,
            'kendali_apbd_kab' => 2,
        ]);
        Pengamatan::create($common + [
            'tanggal_pengamatan' => '2025-12-10',
            'serangan_ringan' => 5,
            'serangan_jumlah' => 5,
        ]);

        $this->actingAs($admin)->get(route('laporan.tahunan', ['tahun' => 2026, 'uppt_id' => $uppt->id]))
            ->assertOk()
            ->assertSee('Histori Tahun 2026')
            ->assertViewHas('data', function ($data): bool {
                return $data->count() === 2
                    && $data->first()->tanggal_pengamatan->toDateString() === '2026-01-10'
                    && (float) $data->first()->serangan_jumlah === 2.0
                    && (float) $data->first()->kondisi_serangan_ha === 2.0
                    && $data->last()->tanggal_pengamatan->toDateString() === '2026-02-10'
                    && (float) $data->last()->serangan_jumlah === 3.0
                    && (float) $data->last()->kondisi_serangan_ha === 3.0;
            });
    }

    public function test_annual_history_does_not_preselect_an_uppt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $this->actingAs($admin)->get(route('laporan.tahunan'))
            ->assertOk()
            ->assertViewHas('upptId', '');
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

        // Data 1: UPPT 1, Jan 2026 (Triwulan 1, 2026)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2026-01-15']));

        // Data 2: UPPT 2, Jun 2026 (bulan terakhir Triwulan 2)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt2->id, 'tanggal_pengamatan' => '2026-06-10']));

        // Data 3: UPPT 1, Des 2025 (Triwulan 4, 2025)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2025-12-20']));

        // 1. Uji Filter Bulanan (Januari 2026) & UPPT 1
        $resBulan = $this->actingAs($admin)->get("/laporan?jenis=bulanan&bulan=1&tahun=2026&uppt_id={$uppt1->id}");
        $resBulan->assertViewHas('data', function ($data) {
            return $data->count() === 1 && $data->first()->tanggal_pengamatan->format('Y-m-d') === '2026-01-15';
        });

        // 2. Uji Filter Triwulan (Triwulan 2 2026) - hanya bulan terakhir
        $resTriwulan = $this->actingAs($admin)->get('/laporan?jenis=triwulan&triwulan=2&tahun=2026&uppt_id=');
        $resTriwulan->assertViewHas('data', function ($data) use ($uppt2) {
            return $data->count() === 1 && $data->first()->uppt_id === $uppt2->id;
        });

        // 3. Laporan tahunan belum tersedia pada tahap ini.
        $this->actingAs($admin)->get('/laporan?jenis=tahunan&tahun=2026&uppt_id=')->assertSessionHasErrors('jenis');

        // 4. Uji pencarian pada laporan bulanan.
        $resSearch = $this->actingAs($admin)->get('/laporan?jenis=bulanan&bulan=1&tahun=2026&search=Padi&uppt_id=');
        $resSearch->assertViewHas('data', function ($data) {
            return $data->count() === 1;
        });

        // 5. Uji Export Excel (Filter Triwulan 1 2026, Semua UPPT)
        $resExcel = $this->actingAs($admin)->get('/laporan/export/excel?jenis=triwulan&triwulan=1&tahun=2026&uppt_id=');
        $resExcel->assertOk();
        $resExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $resExcel->assertHeader('content-disposition', 'attachment; filename=Laporan_Triwulan_1_2026.xlsx');

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

    public function test_quarterly_report_does_not_fall_back_to_prior_month(): void
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
            'tanggal_pengamatan' => '2026-04-15',
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 2,
        ]);

        $this->actingAs($admin)->get('/laporan?jenis=triwulan&triwulan=2&tahun=2026&uppt_id=')
            ->assertViewHas('data', fn ($data): bool => $data->isEmpty());
    }
}
