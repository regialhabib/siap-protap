<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengamatan extends Model
{
    protected $fillable = [
        'user_id', 'uppt_id', 'komoditas_id', 'opt_id',
        'tanggal_pengamatan', 'luas_komoditi_ha', 'serangan_ringan',
        'serangan_sedang', 'serangan_berat', 'serangan_jumlah',
        'kendali_apbd_kab', 'kendali_apbd_prov', 'kendali_masyarakat',
        'kendali_apbn', 'kondisi_serangan', 'kecamatan_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengamatan' => 'date',
        ];
    }

    public function uppt(): BelongsTo
    {
        return $this->belongsTo(Uppt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function opt(): BelongsTo
    {
        return $this->belongsTo(Opt::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
