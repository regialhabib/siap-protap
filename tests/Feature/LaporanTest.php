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
        $response->assertSee('Export Laporan');
    }

    public function test_popt_cannot_access_laporan_page()
    {
        $popt = User::factory()->create(['role' => 'popt']);
        
        $response = $this->actingAs($popt)->get('/laporan');
        
        $response->assertStatus(403);
    }

    public function test_laporan_time_filters_and_export_works()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $uppt1 = $this->createUppt();
        $uppt2 = Uppt::create(['nama_uppt' => 'UPPT Lain', 'kabupaten_id' => 1]);
        
        $komoditas = Komoditas::create(['nama_komoditas' => 'Padi']);
        $opt = Opt::create(['nama_opt' => 'Wereng']);

        $commonData = [
            'user_id' => $admin->id, 'komoditas_id' => $komoditas->id, 'opt_id' => $opt->id,
            'luas_komoditi_ha' => 10, 'serangan_ringan' => 0, 'serangan_sedang' => 0, 'serangan_berat' => 0,
            'serangan_jumlah' => 0, 'kondisi_serangan' => 'Aman'
        ];

        // Data 1: UPPT 1, Jan 2026 (Triwulan 1, 2026)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2026-01-15']));
        
        // Data 2: UPPT 2, Apr 2026 (Triwulan 2, 2026)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt2->id, 'tanggal_pengamatan' => '2026-04-10']));

        // Data 3: UPPT 1, Des 2025 (Triwulan 4, 2025)
        Pengamatan::create(array_merge($commonData, ['uppt_id' => $uppt1->id, 'tanggal_pengamatan' => '2025-12-20']));

        // 1. Uji Filter Bulanan (Januari 2026) & UPPT 1
        $resBulan = $this->actingAs($admin)->get("/laporan?jenis=bulanan&bulan=1&tahun=2026&uppt_id={$uppt1->id}");
        $resBulan->assertViewHas('data', function ($data) {
            return $data->count() === 1 && $data->first()->tanggal_pengamatan->format('Y-m-d') === '2026-01-15';
        });

        // 2. Uji Filter Triwulan (Triwulan 2 2026) - Semua UPPT
        $resTriwulan = $this->actingAs($admin)->get('/laporan?jenis=triwulan&triwulan=2&tahun=2026&uppt_id=');
        $resTriwulan->assertViewHas('data', function ($data) use ($uppt2) {
            return $data->count() === 1 && $data->first()->uppt_id === $uppt2->id;
        });

        // 3. Uji Filter Tahunan (2026) - Semua UPPT
        $resTahunan = $this->actingAs($admin)->get('/laporan?jenis=tahunan&tahun=2026&uppt_id=');
        $resTahunan->assertViewHas('data', function ($data) {
            return $data->count() === 2; // Jan & Apr 2026
        });

        // 4. Uji Pencarian (Search) di Tahun 2026 (default)
        $resSearch = $this->actingAs($admin)->get('/laporan?jenis=tahunan&tahun=2026&search=Padi&uppt_id=');
        $resSearch->assertViewHas('data', function ($data) {
            return $data->count() === 2; // Padi ada di 2 data tahun 2026 (Jan & Apr)
        });

        // 5. Uji Export Excel (Filter Triwulan 1 2026, Semua UPPT)
        $resExcel = $this->actingAs($admin)->get('/laporan/export/excel?jenis=triwulan&triwulan=1&tahun=2026&uppt_id=');
        $resExcel->assertStatus(200);
        $resExcel->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $resExcel->assertHeader('content-disposition', 'attachment; filename=Laporan_Triwulan_1_2026.xlsx');

        // 6. Uji Export PDF (Filter Bulanan Des 2025, UPPT 1)
        $resPdf = $this->actingAs($admin)->get("/laporan/export/pdf?jenis=bulanan&bulan=12&tahun=2025&uppt_id={$uppt1->id}");
        $resPdf->assertStatus(200);
        $resPdf->assertHeader('content-type', 'application/pdf');
    }
}
