<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Uppt;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use Illuminate\Support\Facades\DB;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_are_calculated_correctly()
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
        
        $response->assertStatus(200);
        
        // Assert that sum of serangan_jumlah is 15 * 6 = 90
        $response->assertSee('90.00');
        // Assert that distinct komoditas is 2
        $response->assertSee('2');
        // Assert that total pengamatan is 15
        $response->assertSee('15');
    }
}
