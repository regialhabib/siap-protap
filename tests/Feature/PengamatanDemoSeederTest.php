<?php

namespace Tests\Feature;

use App\Models\Pengamatan;
use App\Models\User;
use App\Services\PengamatanHistoryService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\KecamatanSeeder;
use Database\Seeders\PengamatanDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengamatanDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_replaces_every_observation_with_twelve_valid_months_for_twelve_chains(): void
    {
        $this->travelTo('2026-10-26');
        $this->seed([DatabaseSeeder::class, KecamatanSeeder::class]);
        $old = Pengamatan::create([
            'user_id' => User::first()->id, 'uppt_id' => 2, 'komoditas_id' => 1, 'opt_id' => 1,
            'tanggal_pengamatan' => '2026-03-01', 'luas_komoditi_ha' => 5,
        ]);

        $this->seed(PengamatanDemoSeeder::class);

        $this->assertModelMissing($old);
        $observations = Pengamatan::with('user')->get();
        $this->assertCount(144, $observations);
        $this->assertSame(4, $observations->pluck('uppt_id')->unique()->count());
        $this->assertTrue($observations->every(fn (Pengamatan $pengamatan): bool => $pengamatan->user->uppt_id === $pengamatan->uppt_id
            && (float) $pengamatan->serangan_jumlah === (float) $pengamatan->serangan_ringan + (float) $pengamatan->serangan_berat));

        $chains = app(PengamatanHistoryService::class)->monthlyChains($observations, 2026);
        $this->assertCount(12, $chains);
        foreach ($chains as $chain) {
            $this->assertCount(12, array_filter($chain->months, fn (?array $month): bool => $month !== null && $month['ada_input']));
            foreach ($chain->months as $month) {
                $this->assertGreaterThanOrEqual(0, $month['kondisi_akhir']);
                $this->assertLessThanOrEqual($month['luas_komoditi_ha'], $month['kondisi_akhir']);
            }
        }

        $firstRun = $observations->sum('serangan_jumlah');
        $this->seed(PengamatanDemoSeeder::class);
        $this->assertEquals($firstRun, Pengamatan::sum('serangan_jumlah'));
    }
}
