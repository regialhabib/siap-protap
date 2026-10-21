<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengamatan extends Model
{
    protected $fillable = [
        'user_id', 'uppt_id', 'komoditas_id', 'opt_id', 'jenis_opt_id',
        'tanggal_pengamatan', 'luas_komoditi_ha', 'serangan_ringan',
        'serangan_berat', 'serangan_jumlah',
        'kendali_apbd_kab', 'kendali_apbd_prov', 'kendali_masyarakat',
        'kendali_apbn', 'kecamatan_id', 'cara_pengendalian',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengamatan' => 'date',
        ];
    }

    /**
     * Batasi pengamatan pada rentang tanggal (inklusif di kedua ujung).
     */
    #[Scope]
    protected function observedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): void
    {
        $query->whereDate('tanggal_pengamatan', '>=', $from->toDateString())
            ->whereDate('tanggal_pengamatan', '<=', $until->toDateString());
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

    public function jenisOpt(): BelongsTo
    {
        return $this->belongsTo(JenisOpt::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
