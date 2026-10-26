<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\JenisOpt;
use App\Models\Kecamatan;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use App\Models\Uppt;
use App\Models\User;
use App\Services\PeriodePengamatan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Random\Engine\Mt19937;
use Random\Randomizer;
use RuntimeException;

/**
 * Data contoh pengamatan: SEMUA pengamatan lama dihapus, lalu diisi ulang 4 UPPT × 3 rantai,
 * satu pengamatan per bulan selama Periode 2026 (Februari 2026 – Januari 2027).
 * Hasilnya selalu sama setiap dijalankan. Jalankan dengan: php artisan db:seed --class=PengamatanDemoSeeder
 */
class PengamatanDemoSeeder extends Seeder
{
    private const PERIODE = 2026;

    /**
     * Tingkat serangan per bulan (Februari–Januari): lebih tinggi di musim hujan.
     *
     * @var list<float>
     */
    private const MUSIM = [1.0, 1.2, 0.9, 0.6, 0.4, 0.3, 0.4, 0.7, 1.1, 1.3, 1.4, 1.2];

    /**
     * Hari pengamatan per UPPT dan rantainya: [kecamatan, komoditas, OPT, jenis OPT, luas komoditi (Ha)].
     *
     * @var array<string, array{hari: int, rantai: list<array{0: string, 1: string, 2: string, 3: ?string, 4: float}>}>
     */
    private const DATA = [
        'UPPT Rimbo Bujang' => ['hari' => 10, 'rantai' => [
            ['Rimbo Bujang', 'Karet', 'Gugur Daun', 'Corynespora cassiicola', 120.0],
            ['Rimbo Ulu', 'Kelapa Sawit', 'Ulat Api', null, 85.0],
            ['Rimbo Ilir', 'Karet', 'Jamur Akar Putih', null, 60.0],
        ]],
        'UPPT Sengeti' => ['hari' => 14, 'rantai' => [
            ['Maro Sebo', 'Kelapa Sawit', 'Kumbang Tanduk', 'Oryctes rhinoceros', 95.0],
            ['Sekernan', 'Karet', 'Gugur Daun', 'Corynespora cassiicola', 70.0],
            ['Taman Rajo', 'Kelapa Dalam', 'Kumbang Tanduk', 'Oryctes rhinoceros', 40.0],
        ]],
        'UPPT Muara Siau' => ['hari' => 18, 'rantai' => [
            ['Muara Siau', 'Karet', 'Jamur Akar Putih', null, 75.0],
            ['Jangkat', 'Kopi', 'Penggerek Batang', null, 50.0],
            ['Lembah Masurai', 'Kelapa Sawit', 'Babi Hutan', null, 65.0],
        ]],
        'UPPT Kebun IX' => ['hari' => 22, 'rantai' => [
            ['Sungai Gelam', 'Kelapa Sawit', 'Ulat Kantong', null, 110.0],
            ['Kumpeh Ulu', 'Kelapa Sawit', 'Ulat Api', null, 90.0],
            ['Kumpeh', 'Kelapa Dalam', 'Kumbang Tanduk', 'Oryctes rhinoceros', 45.0],
        ]],
    ];

    /** @var array<string, string> */
    private const CARA_PENGENDALIAN = [
        'Gugur Daun' => 'Penyemprotan fungisida berbahan aktif heksakonazol dan pemupukan berimbang.',
        'Ulat Api' => 'Penyemprotan insektisida nabati dan pengumpulan kepompong.',
        'Ulat Kantong' => 'Pemangkasan pelepah terserang dan penyemprotan Bacillus thuringiensis.',
        'Jamur Akar Putih' => 'Pembongkaran tanaman sakit, penaburan belerang, dan aplikasi Trichoderma.',
        'Kumbang Tanduk' => 'Pemasangan perangkap feromon dan sanitasi batang lapuk.',
        'Penggerek Batang' => 'Pemangkasan dan pemusnahan batang terserang.',
        'Babi Hutan' => 'Pemasangan pagar kebun dan perburuan bersama warga.',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('PengamatanDemoSeeder menghapus semua pengamatan dan tidak boleh dijalankan di production.');

            return;
        }

        DB::transaction(function (): void {
            Pengamatan::query()->delete();

            foreach (self::DATA as $namaUppt => $upptData) {
                $uppt = Uppt::where('nama_uppt', $namaUppt)->firstOrFail();
                $petugas = $this->petugas($uppt);

                foreach ($upptData['rantai'] as $urutan => $rantai) {
                    $this->seedChain($uppt, $petugas, $rantai, $upptData['hari'] + $urutan);
                }
            }
        });

        $this->command?->info('Pengamatan lama dihapus; '.Pengamatan::count().' pengamatan contoh Periode '.self::PERIODE.' dibuat.');
    }

    /**
     * Satu rantai 12 bulan. Kondisi = kondisi sebelumnya + serangan baru − pengendalian, selalu 0 sampai luas komoditi.
     *
     * @param  array{0: string, 1: string, 2: string, 3: ?string, 4: float}  $rantai
     */
    private function seedChain(Uppt $uppt, User $petugas, array $rantai, int $hari): void
    {
        [$namaKecamatan, $namaKomoditas, $namaOpt, $namaJenisOpt, $luas] = $rantai;

        $kecamatan = Kecamatan::where('uppt_id', $uppt->id)->where('nama_kecamatan', $namaKecamatan)->first()
            ?? throw new RuntimeException("Kecamatan {$namaKecamatan} tidak ada di {$uppt->nama_uppt}. Jalankan KecamatanSeeder dulu.");
        $komoditas = Komoditas::firstOrCreate(['nama_komoditas' => $namaKomoditas]);
        $opt = Opt::firstOrCreate(['nama_opt' => $namaOpt]);
        $jenisOpt = $namaJenisOpt === null ? null : JenisOpt::firstOrCreate(['opt_id' => $opt->id, 'nama_jenis_opt' => $namaJenisOpt]);

        $random = new Randomizer(new Mt19937(crc32($uppt->nama_uppt.$namaKecamatan.$namaKomoditas.$namaOpt)));
        $kondisi = 0.0;

        foreach (PeriodePengamatan::months(self::PERIODE) as $index => $month) {
            $seranganBaru = $this->halfHectare($luas * 0.04 * self::MUSIM[$index] * $random->getFloat(0.6, 1.4));
            $seranganBaru = min($seranganBaru, $this->halfHectare($luas * 0.6 - $kondisi));
            $berat = $this->halfHectare($seranganBaru * $random->getFloat(0.15, 0.3));
            $batas = $kondisi + $seranganBaru;
            $pengendalian = min($batas, $this->halfHectare($batas * $random->getFloat(0.4, 0.8)));
            $kendali = $this->splitControl($pengendalian, $index);
            $kondisi = $batas - $pengendalian;

            $tanggal = CarbonImmutable::create($month->year, $month->month, $hari);
            $diinput = $tanggal->setTime(16, 0);

            $pengamatan = new Pengamatan([
                'user_id' => $petugas->id,
                'uppt_id' => $uppt->id,
                'kecamatan_id' => $kecamatan->id,
                'komoditas_id' => $komoditas->id,
                'opt_id' => $opt->id,
                'jenis_opt_id' => $jenisOpt?->id,
                'tanggal_pengamatan' => $tanggal->toDateString(),
                'luas_komoditi_ha' => $luas,
                'serangan_ringan' => $seranganBaru - $berat,
                'serangan_berat' => $berat,
                'serangan_jumlah' => $seranganBaru,
                ...$kendali,
                'cara_pengendalian' => $pengendalian > 0 ? self::CARA_PENGENDALIAN[$namaOpt] : null,
            ]);
            // Data lama tercatat diinput pada hari pengamatannya agar masa edit 2 hari sudah lewat.
            $pengamatan->created_at = $pengamatan->updated_at = $diinput->isFuture() ? now() : $diinput;
            $pengamatan->save();
        }
    }

    /**
     * Pengendalian dibagi ke sumber dana: APBD II dan swadaya setiap bulan, APBD I dan APBN di bulan tertentu.
     *
     * @return array{kendali_apbd_prov: float, kendali_apbd_kab: float, kendali_masyarakat: float, kendali_apbn: float}
     */
    private function splitControl(float $pengendalian, int $index): array
    {
        $provinsi = in_array($index, [1, 6, 10], true) ? $this->halfHectare($pengendalian * 0.3) : 0.0;
        $apbn = $index === 4 ? $this->halfHectare($pengendalian * 0.4) : 0.0;
        $sisa = $pengendalian - $provinsi - $apbn;
        $masyarakat = $this->halfHectare($sisa * 0.35);

        return [
            'kendali_apbd_prov' => $provinsi,
            'kendali_apbd_kab' => $sisa - $masyarakat,
            'kendali_masyarakat' => $masyarakat,
            'kendali_apbn' => $apbn,
        ];
    }

    /**
     * POPT pertama UPPT ini; dibuatkan akun contoh bila belum ada.
     */
    private function petugas(Uppt $uppt): User
    {
        return User::where('role', UserRole::Popt)->where('uppt_id', $uppt->id)->orderBy('id')->first()
            ?? User::create([
                'name' => 'Petugas '.Str::after($uppt->nama_uppt, 'UPPT '),
                'email' => 'popt.'.Str::slug(Str::after($uppt->nama_uppt, 'UPPT '), '').'@siap-protap.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Popt,
                'status' => UserStatus::Aktif,
                'uppt_id' => $uppt->id,
            ]);
    }

    private function halfHectare(float $hektare): float
    {
        return max(0.0, round($hektare * 2) / 2);
    }
}
