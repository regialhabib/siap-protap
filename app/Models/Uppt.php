<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uppt extends Model
{
    protected $fillable = ['nama_uppt', 'kabupaten_id'];

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pengamatans(): HasMany
    {
        return $this->hasMany(Pengamatan::class);
    }

    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class);
    }
}
