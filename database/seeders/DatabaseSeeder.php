<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kabupaten;
use App\Models\Uppt;
use App\Models\Komoditas;
use App\Models\Opt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Kabupaten & UPPT (Berdasarkan Excel)
        $dataWilayah = [
            'Tebo' => ['UPPT Rimbo Bujang', 'UPPT Tebo Ulu'],
            'Kerinci' => ['UPPT Muaro Lolo', 'UPPT Tamiai', 'UPPT Simpang Tutup'],
            'Merangin' => ['UPPT Pamenang', 'UPPT Hitam Ulu', 'UPPT Muara Siau', 'UPPT Sungai Manau'],
            'Batanghari' => ['UPPT Muara Bulian', 'UPPT Mata Gual'],
            'Muaro Jambi' => ['UPPT Kebun IX', 'UPPT Sengeti', 'UPPT Sungai Tiga'],
            'Sarolangun' => ['UPPT Sarolangun', 'UPPT Pauh', 'UPPT limun'],
            'Bungo' => ['UPPT Bagian Barat', 'UPPT Bagian Timur'],
            'Tanjung Jabung Timur' => ['UPPT Sabak', 'UPPT Nipah Panjang'],
            'Tanjung Jabung Barat' => ['UPPT Tungkal Ilir', 'UPPT Tungkal Ulu'],
        ];

        foreach ($dataWilayah as $namaKabupaten => $uppts) {
            $kabupaten = Kabupaten::create(['nama_kabupaten' => $namaKabupaten]);
            foreach ($uppts as $namaUppt) {
                Uppt::create([
                    'kabupaten_id' => $kabupaten->id,
                    'nama_uppt' => $namaUppt
                ]);
            }
        }

        // 2. Data Komoditas
        $komoditas = ['Kelapa Sawit', 'Karet', 'Kopi', 'Kakao', 'Kelapa Dalam', 'Pinang'];
        foreach ($komoditas as $k) {
            Komoditas::create(['nama_komoditas' => $k]);
        }

        // 3. Data OPT (Hama & Penyakit)
        $opts = ['Ulat Api', 'Ulat Kantong', 'Jamur Akar Putih', 'Gugur Daun', 'Babi Hutan', 'Tupai', 'Penggerek Batang'];
        foreach ($opts as $o) {
            Opt::create(['nama_opt' => $o]);
        }

        // 4. Akun Admin (UPTD BPTP Provinsi)
        User::create([
            'name' => 'Administrator BPTP',
            'email' => 'admin@siap-protap.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'uppt_id' => null,
        ]);

        // 5. Akun Petugas POPT (Sample)
        // Ambil salah satu UPPT, misalnya UPPT Rimbo Bujang
        $upptSample = Uppt::where('nama_uppt', 'UPPT Rimbo Bujang')->first();
        if ($upptSample) {
            User::create([
                'name' => 'Petugas Rimbo Bujang',
                'email' => 'popt.rimbobujang@siap-protap.com',
                'password' => Hash::make('password'),
                'role' => 'popt',
                'uppt_id' => $upptSample->id,
            ]);
        }
    }
}
