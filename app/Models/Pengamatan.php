<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengamatan extends Model
{
    /** Lama (hari) petugas POPT masih dapat mengubah atau menghapus data sejak diinput. */
    public const EDIT_WINDOW_DAYS = 2;

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
     *
     * Memakai rentang setengah-terbuka tanpa fungsi DATE() agar index kolom
     * tetap terpakai dan nilai bertimestamp pada hari terakhir tetap ikut.
     */
    #[Scope]
    protected function observedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): void
    {
        $query->where('tanggal_pengamatan', '>=', $from->toDateString())
            ->where('tanggal_pengamatan', '<', $until->copy()->addDay()->toDateString());
    }

    public function isWithinEditWindow(): bool
    {
        return $this->created_at !== null && $this->created_at->copy()->addDays(self::EDIT_WINDOW_DAYS)->isFuture();
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
