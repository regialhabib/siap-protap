<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Uppt;
use App\Models\Komoditas;
use App\Models\Opt;
use App\Models\Pengamatan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Pastikan data referensi ada
        $uppts = Uppt::all();
        if ($uppts->isEmpty()) {
            DB::table('kabupatens')->insertOrIgnore(['id' => 1, 'nama_kabupaten' => 'Rokan Hilir']);
            Uppt::create(['nama_uppt' => 'UPPT Bagan Sinembah', 'kabupaten_id' => 1]);
            Uppt::create(['nama_uppt' => 'UPPT Tanah Putih', 'kabupaten_id' => 1]);
            Uppt::create(['nama_uppt' => 'UPPT Kubu', 'kabupaten_id' => 1]);
            $uppts = Uppt::all();
        }

        $komoditas = Komoditas::all();
        if ($komoditas->isEmpty()) {
            Komoditas::create(['nama_komoditas' => 'Karet']);
            Komoditas::create(['nama_komoditas' => 'Kelapa Sawit']);
            $komoditas = Komoditas::all();
        }

        $opts = Opt::all();
        if ($opts->isEmpty()) {
            Opt::create(['nama_opt' => 'Gugur Daun Karet (Corynespora)']);
            Opt::create(['nama_opt' => 'Kumbang Tanduk (Oryctes)']);
            $opts = Opt::all();
        }

        $kondisiOptions = ['Aman', 'Terkendali', 'Waspada', 'Eradikasi'];

        // 2. Buat 3 Petugas POPT
        for ($i = 1; $i <= 3; $i++) {
            $user = User::firstOrCreate(
                ['email' => "petugas{$i}@gmail.com"],
                [
                    'name' => "Petugas POPT {$i}",
                    'role' => 'popt',
                    'password' => Hash::make('password')
                ]
            );

            // 3. Buat 10 data pengamatan acak untuk setiap petugas
            for ($j = 0; $j < 10; $j++) {
                // Tanggal acak dalam 3 bulan terakhir
                $tanggal = Carbon::now()->subDays(rand(0, 90));
                
                // Nilai serangan acak
                $ringan = rand(0, 50) / 10;
                $sedang = rand(0, 30) / 10;
                $berat = rand(0, 10) / 10;
                $jumlah = $ringan + $sedang + $berat;

                Pengamatan::create([
                    'user_id' => $user->id,
                    'uppt_id' => $uppts->random()->id,
                    'komoditas_id' => $komoditas->random()->id,
                    'opt_id' => $opts->random()->id,
                    'tanggal_pengamatan' => $tanggal->format('Y-m-d'),
                    
                    'luas_komoditi_ha' => rand(10, 100) + (rand(0,9)/10),
                    
                    'serangan_ringan' => $ringan,
                    'serangan_sedang' => $sedang,
                    'serangan_berat' => $berat,
                    'serangan_jumlah' => $jumlah,
                    
                    'kendali_apbd_kab' => rand(0, 10) / 10,
                    'kendali_apbd_prov' => rand(0, 10) / 10,
                    'kendali_masyarakat' => rand(0, 5) / 10,
                    'kendali_apbn' => 0,
                    
                    'kondisi_serangan' => $kondisiOptions[array_rand($kondisiOptions)]
                ]);
            }
        }
    }
}
