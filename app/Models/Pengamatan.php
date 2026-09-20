<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengamatan extends Model
{
    protected $fillable = [
        'user_id', 'uppt_id', 'komoditas_id', 'opt_id', 
        'tanggal_pengamatan', 'luas_komoditi_ha', 'serangan_ringan', 
        'serangan_sedang', 'serangan_berat', 'serangan_jumlah', 
        'kendali_apbd_kab', 'kendali_apbd_prov', 'kendali_masyarakat', 
        'kendali_apbn', 'kondisi_serangan'
    ];

    protected $casts = [
        'tanggal_pengamatan' => 'date',
    ];

    public function uppt()
    {
        return $this->belongsTo(Uppt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function opt()
    {
        return $this->belongsTo(Opt::class);
    }
}
