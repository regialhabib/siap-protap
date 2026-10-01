<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Uppt;
use App\Models\Kecamatan;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'UPPT Rimbo Bujang' => ['Rimbo Bujang', 'Rimbo Ulu', 'Rimbo Ilir'],
            'UPPT Tebo Ulu' => ['Tebo Ilir', 'Tebo Ulu', 'Sumay', 'Muara Tabir', 'Serai Serumpun', 'Tengah Ilir', 'VII Koto Ilir', 'VII Koto'],
            'UPPT Simpang Tutup' => ['Air Hangat', 'Air Hangat Barat', 'Gunung Kerinci', 'Gunung Raya', 'Gunung Tujuh', 'Kayu Aro', 'Kayu Aro Barat', 'Siulak', 'Siulak Mukai', 'Tanah Cogok'],
            'UPPT Tamiai' => ['Air Hangat Timur', 'Batang Merangin', 'Danau Kerinci', 'Sitinjau Laut'],
            'UPPT Muaro Lolo' => ['Bukit Kerman', 'Danau Kerinci Barat', 'Depati Tujuh', 'Keliling Danau'],
            'UPPT Pamenang' => ['Pemenang', 'Pemenang Barat', 'Renah Pemenang', 'Pemenang Selatan'],
            'UPPT Sungai Manau' => ['Sungai Manau', 'Pangkalan Jambu', 'Renah Pemarap', 'Bangko Barat', 'Batang Masumai', 'Nalo Tatan'],
            'UPPT Muara Siau' => ['Muara Siau', 'Lembah Masurai', 'Jangkat', 'Jangkat Timur', 'Tiang Pumpung'],
            'UPPT Hitam Ulu' => ['Tabir Lintas', 'Tabir Selatan', 'Tabir', 'Tabir Ilir', 'Tabir Ulu', 'Tabir Barat', 'Margo Tabir', 'Tabir Timur'],
            'UPPT Muara Bulian' => ['Muara Bulian', 'Muaro Sebo Ilir', 'Bajubang', 'Muara Tembasi', 'Mersam', 'Pemayung'],
            'UPPT Mata Gual' => ['Batin XXIV', 'Sebo Ulu'],
            'UPPT Kebun IX' => ['Sungai Gelam', 'Kumpeh Ulu', 'Kumpeh'],
            'UPPT Sengeti' => ['Maro Sebo', 'Sekernan', 'Taman Rajo'],
            'UPPT Sungai Tiga' => ['Jambi Luar Kota', 'Bahar Selatan', 'Bahar Utara', 'Mestong', 'Sungai Bahar'],
            'UPPT Sarolangun' => ['Pelawan', 'Kota Sarolangun', 'Bathin VIII', 'Singkut'],
            'UPPT Pauh' => ['Pauh', 'Mandiangin', 'Air Hitam'],
            'UPPT limun' => ['Limun', 'Batang Asai', 'Cermin Nan Gedang'],
            'UPPT Bagian Barat' => ['Jujuhan', 'Jujuhan Ilir', 'Bathin II Babeko', 'Tanah Sepenggal', 'Tanah Sepenggal Lintas', 'Tanah Tumbuh', 'Limbur Lubuk Mengkuang'],
            'UPPT Bagian Timur' => ['Bathin II Pelayang', 'Bathin III', 'Bathin III Ulu', 'Bungo Dani', 'Muko-Muko Bathin VII', 'Pasar Muara Bungo', 'Pelepat', 'Pelepat Ilir', 'Rantau Pandan', 'Rimbo Tengah'],
            'UPPT Sabak' => ['Dendang', 'Geragai', 'Kuala Jambi', 'Mendahara', 'Mendahara Ulu', 'Muara Sabak Timur', 'Muara Sabak Barat'],
            'UPPT Nipah Panjang' => ['Nipah Panjang', 'Rantau Rasau', 'Sadu', 'Berbak'],
            'UPPT Tungkal Ilir' => ['Tungkal Ilir', 'Betara', 'Kuala Betara', 'Bram Itam', 'Seberang Kota'],
            'UPPT Tungkal Ulu' => ['Merlung', 'Tebing Tinggi', 'Tungkal Ulu', 'Batang Asam', 'Muara Papalik', 'Pangabuan', 'Renah Mandaluh', 'Senyerang']
        ];

        foreach ($data as $upptName => $kecamatanList) {
            $uppt = Uppt::where('nama_uppt', $upptName)->first();
            
            if ($uppt) {
                foreach ($kecamatanList as $namaKecamatan) {
                    Kecamatan::firstOrCreate([
                        'uppt_id' => $uppt->id,
                        'nama_kecamatan' => $namaKecamatan
                    ]);
                }
            } else {
                $this->command->warn("UPPT dengan nama '{$upptName}' tidak ditemukan.");
            }
        }
    }
}
