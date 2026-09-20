<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Uppt;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function createUppt()
    {
        \Illuminate\Support\Facades\DB::table('kabupatens')->insert(['id' => 1, 'nama_kabupaten' => 'Test Kab']);
        return Uppt::create(['nama_uppt' => 'Test UPPT', 'kabupaten_id' => 1]);
    }

    public function test_admin_can_access_laporan_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $response = $this->actingAs($admin)->get('/laporan');
        
        $response->assertStatus(200);
        $response->assertSee('Rekap Laporan Pengamatan');
    }

    public function test_popt_cannot_access_laporan_page()
    {
        $popt = User::factory()->create(['role' => 'popt']);
        
        $response = $this->actingAs($popt)->get('/laporan');
        
        $response->assertStatus(403);
    }

    public function test_laporan_with_triwulan_filter_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();
        
        $response = $this->actingAs($admin)->get('/laporan?jenis=triwulan&triwulan=2&tahun=2026');
        
        $response->assertStatus(200);
    }

    public function test_laporan_export_excel_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $response = $this->actingAs($admin)->get('/laporan/export/excel');
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_laporan_export_pdf_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createUppt();

        $response = $this->actingAs($admin)->get('/laporan/export/pdf');
        
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
