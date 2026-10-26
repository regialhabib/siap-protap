<?php

namespace Tests\Feature;

use App\Models\JenisOpt;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_dashboard_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $komoditas1 = Komoditas::create(['nama_komoditas' => 'Karet']);
        $komoditas2 = Komoditas::create(['nama_komoditas' => 'Sawit']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);

        // Insert some records
        for ($i = 0; $i < 15; $i++) {
            Pengamatan::create([
                'user_id' => $admin->id,
                'uppt_id' => $uppt->id,
                'komoditas_id' => $i % 2 == 0 ? $komoditas1->id : $komoditas2->id,
                'opt_id' => $opt->id,
                'tanggal_pengamatan' => date('Y-m-d'),
                'luas_komoditi_ha' => 10,
                'serangan_ringan' => 1,
                'serangan_berat' => 3,
                'serangan_jumlah' => 4,
                'kendali_apbd_kab' => 0,
                'kendali_apbd_prov' => 0,
                'kendali_masyarakat' => 0,
                'kendali_apbn' => 0,
            ]);
        }

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();

        $response->assertViewHas('stats', fn (array $stats): bool => (float) $stats['total_luas_serangan'] === 60.0
            && (int) $stats['komoditas_terdampak'] === 2
            && (int) $stats['total_pengamatan'] === 15);
        $response->assertSee('>60</div>', false);
    }

    public function test_calculates_dashboard_uppt_chart_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);

        // Buat 3 UPPT (Total UPPT = 3)
        $uppt1 = Uppt::create(['nama_uppt' => 'UPPT A', 'kabupaten_id' => 1]);
        $uppt2 = Uppt::create(['nama_uppt' => 'UPPT B', 'kabupaten_id' => 1]);
        $uppt3 = Uppt::create(['nama_uppt' => 'UPPT C', 'kabupaten_id' => 1]);

        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);

        // UPPT 1 melakukan pengamatan hari ini
        Pengamatan::create([
            'user_id' => $admin->id,
            'uppt_id' => $uppt1->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'tanggal_pengamatan' => date('Y-m-d'), // bulan ini
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 0, 'serangan_berat' => 0, 'serangan_jumlah' => 0,
            'kendali_apbd_kab' => 0, 'kendali_apbd_prov' => 0, 'kendali_masyarakat' => 0, 'kendali_apbn' => 0,
        ]);

        // UPPT 2 melakukan pengamatan bulan lalu (harus masuk hitungan "belum" untuk bulan ini)
        Pengamatan::create([
            'user_id' => $admin->id,
            'uppt_id' => $uppt2->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'tanggal_pengamatan' => Carbon::now()->subMonths(1)->format('Y-m-d'), // bulan lalu
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 0, 'serangan_berat' => 0, 'serangan_jumlah' => 0,
            'kendali_apbd_kab' => 0, 'kendali_apbd_prov' => 0, 'kendali_masyarakat' => 0, 'kendali_apbn' => 0,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('chartData');

        $chartData = $response->viewData('chartData');

        $this->assertEquals(1, $chartData['sudah']->count());
        $this->assertEquals(2, $chartData['belum']->count());

        $this->assertEquals(33.3, $chartData['persentase_sudah']);
        $this->assertEquals(66.7, $chartData['persentase_belum']);
    }

    public function test_admin_dashboard_shows_uppt_status_and_filter()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();

        // Assert admin sees filter and UPPT status section
        $response->assertSee('x-model="bulan"', false);
        $response->assertSee('x-model="tahun"', false);
        $response->assertSee('Status Pengamatan UPPT');

        // Assert admin does NOT see the pengamatan table
        $response->assertDontSee('Riwayat Pengamatan Terbaru');
    }

    public function test_popt_dashboard_shows_riwayat_pengamatan()
    {
        DB::table('kabupatens')->insertOrIgnore(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::firstOrCreate(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);

        $response = $this->actingAs($popt)->get('/dashboard');

        $response->assertOk();

        // Assert popt sees the pengamatan table
        $response->assertSee('Riwayat Pengamatan Terbaru');

        // Assert popt does NOT see the filter or UPPT status section
        $response->assertDontSee('x-model="bulan"', false);
        $response->assertDontSee('Status Pengamatan UPPT');
    }

    public function test_popt_dashboard_displays_parent_and_child_opt_names(): void
    {
        DB::table('kabupatens')->insertOrIgnore(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::firstOrCreate(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenis = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora cassiicola']);
        Pengamatan::create([
            'user_id' => $popt->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenis->id,
            'tanggal_pengamatan' => now()->toDateString(),
            'luas_komoditi_ha' => 1,
            'serangan_jumlah' => 0,
        ]);

        $this->actingAs($popt)->get('/dashboard')->assertOk()->assertSee('Gugur Daun — Corynespora cassiicola');
    }

    public function test_popt_dashboard_lists_active_chains_with_this_month_status(): void
    {
        $this->travelTo('2026-05-15');
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $sawit = Komoditas::create(['nama_komoditas' => 'Sawit']);
        $kopi = Komoditas::create(['nama_komoditas' => 'Kopi']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $common = ['user_id' => $popt->id, 'uppt_id' => $uppt->id, 'opt_id' => $opt->id, 'luas_komoditi_ha' => 10];
        Pengamatan::create($common + ['komoditas_id' => $karet->id, 'tanggal_pengamatan' => '2026-04-10', 'serangan_ringan' => 3, 'serangan_jumlah' => 3]);
        Pengamatan::create($common + ['komoditas_id' => $sawit->id, 'tanggal_pengamatan' => '2026-05-03', 'serangan_ringan' => 2, 'serangan_jumlah' => 2]);
        Pengamatan::create($common + ['komoditas_id' => $kopi->id, 'tanggal_pengamatan' => '2026-01-20', 'serangan_ringan' => 9, 'serangan_jumlah' => 9]);

        $response = $this->actingAs($popt)->get('/dashboard');

        $response->assertSee('Belum dilaporkan')->assertSee('Sudah dilaporkan');
        $response->assertViewHas('activeChains', fn ($chains): bool => $chains->pluck('komoditas')->all() === ['Karet', 'Sawit']
            && $chains[0]->sudah_lapor === false
            && $chains[0]->kondisi_serangan_ha === 3.0
            && $chains[1]->sudah_lapor === true
            && str_contains($chains[0]->input_url, 'komoditas_id='.$karet->id));
    }

    public function test_popt_dashboard_skips_chains_already_controlled_to_zero(): void
    {
        $this->travelTo('2026-05-15');
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kakao = Komoditas::create(['nama_komoditas' => 'Kakao']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $common = ['user_id' => $popt->id, 'uppt_id' => $uppt->id, 'opt_id' => $opt->id, 'luas_komoditi_ha' => 10];
        Pengamatan::create($common + ['komoditas_id' => $karet->id, 'tanggal_pengamatan' => '2026-04-10', 'serangan_ringan' => 3, 'serangan_jumlah' => 3, 'kendali_apbd_kab' => 3]);
        Pengamatan::create($common + ['komoditas_id' => $kakao->id, 'tanggal_pengamatan' => '2026-05-03', 'serangan_ringan' => 2, 'serangan_jumlah' => 2, 'kendali_apbd_kab' => 2]);

        $response = $this->actingAs($popt)->get('/dashboard');

        $response->assertViewHas('activeChains', fn ($chains): bool => $chains->pluck('komoditas')->all() === ['Kakao']
            && $chains[0]->sudah_lapor === true);
    }

    public function test_admin_dashboard_stats_include_both_month_edges_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $komoditas = Komoditas::create(['nama_komoditas' => 'Karet']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);

        foreach (['2026-04-30', '2026-05-01', '2026-05-31', '2026-06-01'] as $tanggal) {
            Pengamatan::create([
                'user_id' => $admin->id,
                'uppt_id' => $uppt->id,
                'komoditas_id' => $komoditas->id,
                'opt_id' => $opt->id,
                'tanggal_pengamatan' => $tanggal,
                'luas_komoditi_ha' => 10,
                'serangan_ringan' => 1,
                'serangan_berat' => 0,
                'serangan_jumlah' => 1,
            ]);
        }

        $this->actingAs($admin)->get('/dashboard?bulan=5&tahun=2026')
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats): bool => $stats['total_pengamatan'] === 2);
    }

    public function test_admin_dashboard_rejects_invalid_month_and_year_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/dashboard?bulan=abc&tahun=abc')
            ->assertRedirect()
            ->assertSessionHasErrors(['bulan', 'tahun']);
        $this->getJson('/dashboard?bulan=13')->assertUnprocessable()->assertJsonValidationErrors('bulan');
    }

    public function test_popt_dashboard_ignores_month_filters_and_hides_other_uppt_status(): void
    {
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Petugas', 'kabupaten_id' => 1]);
        Uppt::create(['nama_uppt' => 'UPPT Tetangga', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);

        $response = $this->actingAs($popt)->get('/dashboard?bulan=abc');

        $response->assertOk()
            ->assertDontSee('UPPT Tetangga')
            ->assertViewHas('chartData', null);
    }

    public function test_popt_dashboard_renders_one_delete_form_and_entry_details_per_observation(): void
    {
        DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        $uppt = Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
        $popt = User::factory()->create(['role' => 'popt', 'uppt_id' => $uppt->id]);
        $pengamatan = Pengamatan::create([
            'user_id' => $popt->id,
            'uppt_id' => $uppt->id,
            'komoditas_id' => Komoditas::create(['nama_komoditas' => 'Karet'])->id,
            'opt_id' => Opt::create(['nama_opt' => 'Gugur Daun'])->id,
            'tanggal_pengamatan' => '2026-02-10',
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => 3,
            'serangan_jumlah' => 3,
            'kendali_apbd_prov' => 1,
        ]);

        $response = $this->actingAs($popt)->get('/dashboard');

        $response->assertOk()
            ->assertSee('"tanggal":"10 Februari 2026"')
            ->assertSee('"tambah_jumlah":"3"')
            ->assertSee('"kendali_jumlah":"1"');
        $this->assertSame(1, substr_count($response->getContent(), 'action="'.route('pengamatan.destroy', $pengamatan).'"'));
    }
}
