<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisOpt extends Model
{
    protected $fillable = ['opt_id', 'nama_jenis_opt'];

    public function opt(): BelongsTo
    {
        return $this->belongsTo(Opt::class);
    }

    public function pengamatans(): HasMany
    {
        return $this->hasMany(Pengamatan::class);
    }
}
