<?php

namespace Tests\Feature;

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
                'serangan_sedang' => 2,
                'serangan_berat' => 3,
                'serangan_jumlah' => 6, // 1+2+3
                'kendali_apbd_kab' => 0,
                'kendali_apbd_prov' => 0,
                'kendali_masyarakat' => 0,
                'kendali_apbn' => 0,
                'kondisi_serangan' => 'Aman',
            ]);
        }

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();

        // Assert that sum of serangan_jumlah is 15 * 6 = 90
        $response->assertSee('90.00');
        // Assert that distinct komoditas is 2
        $response->assertSee('2');
        // Assert that total pengamatan is 15
        $response->assertSee('15');
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
            'serangan_ringan' => 0, 'serangan_sedang' => 0, 'serangan_berat' => 0, 'serangan_jumlah' => 0,
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
            'serangan_ringan' => 0, 'serangan_sedang' => 0, 'serangan_berat' => 0, 'serangan_jumlah' => 0,
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
}
