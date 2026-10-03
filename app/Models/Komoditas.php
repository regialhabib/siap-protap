<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Komoditas extends Model
{
    protected $fillable = ['nama_komoditas'];

    public function pengamatans(): HasMany
    {
        return $this->hasMany(Pengamatan::class);
    }
}
