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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPengamatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_twelve_months_from_the_sidebar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', ['tahun' => 2026]));

        $response->assertOk()
            ->assertSee('Riwayat Pengamatan Tahunan')
            ->assertSee('Periode Sebelumnya (Ha)')
            ->assertSee('Periode Sekarang (Ha)')
            ->assertSee('Riwayat Pengamatan')
            ->assertViewHas('data', fn ($data): bool => $data->count() === 12 && $data->total() === 12);
    }

    public function test_popt_cannot_access_the_admin_observation_history(): void
    {
        $popt = User::factory()->create(['role' => 'popt']);

        $this->actingAs($popt)->get(route('pengamatan.riwayat.index'))->assertForbidden();
    }

    public function test_monthly_comparison_uses_latest_snapshots_and_shows_missing_period_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupaten = Kabupaten::create(['nama_kabupaten' => 'Kabupaten Uji']);
        $uppt = Uppt::create(['nama_uppt' => 'UPPT Uji', 'kabupaten_id' => $kabupaten->id]);
        $kecamatan = Kecamatan::create(['nama_kecamatan' => 'Kecamatan Uji', 'uppt_id' => $uppt->id]);
        $karet = Komoditas::create(['nama_komoditas' => 'Karet']);
        $kelapa = Komoditas::create(['nama_komoditas' => 'Kelapa']);
        $opt = Opt::create(['nama_opt' => 'Gugur Daun']);
        $jenisOpt = JenisOpt::create(['opt_id' => $opt->id, 'nama_jenis_opt' => 'Corynespora']);

        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2025-12-20', 20, 10);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-01-15', 1, 2, 0.5);
        $this->createObservation($admin, $uppt, $kecamatan, $kelapa, $opt, $jenisOpt, '2026-01-20', 4, 5);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-05', 0.5, 0.25);
        $this->createObservation($admin, $uppt, $kecamatan, $karet, $opt, $jenisOpt, '2026-02-25', 1.5, 0.75);

        $response = $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [
            'tahun' => 2026,
            'kabupaten_id' => $kabupaten->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => $karet->id,
            'jenis_opt_id' => $jenisOpt->id,
        ]));

        $response->assertOk()->assertViewHas('data');
        $rows = $response->viewData('data')->getCollection()->where('is_empty', false);
        $january = $rows->first(fn (object $row): bool => $row->bulan === 1);
        $february = $rows->first(fn (object $row): bool => $row->bulan === 2);

        $this->assertCount(3, $rows);
        $this->assertNull($january->previous);
        $this->assertSame(['ringan' => 1.0, 'berat' => 2.0, 'jumlah' => 3.0, 'kondisi_serangan' => 2.5], $january->current);
        $this->assertSame(['ringan' => 1.0, 'berat' => 2.0, 'jumlah' => 3.0, 'kondisi_serangan' => 2.5], $february->previous);
        $this->assertSame(['ringan' => 3.0, 'berat' => 3.0, 'jumlah' => 6.0, 'kondisi_serangan' => 5.5], $february->current);
        $march = $rows->first(fn (object $row): bool => $row->bulan === 3);
        $this->assertSame(['ringan' => 3.0, 'berat' => 3.0, 'jumlah' => 6.0, 'kondisi_serangan' => 5.5], $march->previous);
        $this->assertNull($march->current);

        $allCommoditiesResponse = $this->get(route('pengamatan.riwayat.index', ['tahun' => 2026]));
        $allCommoditiesResponse->assertViewHas('data', function ($data): bool {
            $februaryRows = $data->getCollection()->where('bulan', 2)->where('is_empty', false)->values();
            $kelapa = $februaryRows->first(fn (object $row): bool => $row->komoditas === 'Kelapa');

            return $kelapa !== null
                && $kelapa->previous === ['ringan' => 4.0, 'berat' => 5.0, 'jumlah' => 9.0, 'kondisi_serangan' => 9.0]
                && $kelapa->current === null;
        });
    }

    public function test_filter_values_must_belong_to_the_selected_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kabupatenA = Kabupaten::create(['nama_kabupaten' => 'Kabupaten A']);
        $kabupatenB = Kabupaten::create(['nama_kabupaten' => 'Kabupaten B']);
        $upptB = Uppt::create(['nama_uppt' => 'UPPT B', 'kabupaten_id' => $kabupatenB->id]);

        $this->actingAs($admin)->get(route('pengamatan.riwayat.index', [
            'kabupaten_id' => $kabupatenA->id,
            'uppt_id' => $upptB->id,
        ]))
            ->assertRedirect()
            ->assertSessionHasErrors('uppt_id');
    }

    private function createObservation(
        User $user,
        Uppt $uppt,
        Kecamatan $kecamatan,
        Komoditas $komoditas,
        Opt $opt,
        JenisOpt $jenisOpt,
        string $date,
        float $seranganRingan,
        float $seranganBerat,
        float $kendaliApbdKab = 0.0,
    ): Pengamatan {
        return Pengamatan::create([
            'user_id' => $user->id,
            'uppt_id' => $uppt->id,
            'kecamatan_id' => $kecamatan->id,
            'komoditas_id' => $komoditas->id,
            'opt_id' => $opt->id,
            'jenis_opt_id' => $jenisOpt->id,
            'tanggal_pengamatan' => $date,
            'luas_komoditi_ha' => 10,
            'serangan_ringan' => $seranganRingan,
            'serangan_berat' => $seranganBerat,
            'serangan_jumlah' => $seranganRingan + $seranganBerat,
            'kendali_apbd_kab' => $kendaliApbdKab,
        ]);
    }
}
