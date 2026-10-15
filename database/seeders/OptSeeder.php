<?php

namespace Database\Seeders;

use App\Models\JenisOpt;
use App\Models\Opt;
use Illuminate\Database\Seeder;

class OptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $optNames = [
            'Ulat Api',
            'Ulat Kantong',
            'Jamur Akar Putih',
            'Gugur Daun',
            'Babi Hutan',
            'Tupai',
            'Penggerek Batang',
            'Kumbang Tanduk',
        ];

        foreach ($optNames as $optName) {
            Opt::firstOrCreate(['nama_opt' => $optName]);
        }

        $jenisOpts = [
            'Gugur Daun' => ['Corynespora cassiicola'],
            'Kumbang Tanduk' => ['Oryctes rhinoceros'],
        ];

        foreach ($jenisOpts as $optName => $jenisOptNames) {
            $opt = Opt::where('nama_opt', $optName)->firstOrFail();

            foreach ($jenisOptNames as $jenisOptName) {
                JenisOpt::firstOrCreate([
                    'opt_id' => $opt->id,
                    'nama_jenis_opt' => $jenisOptName,
                ]);
            }
        }
    }
}
