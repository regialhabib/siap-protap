<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opt extends Model
{
    protected $fillable = ['nama_opt'];

    public function pengamatans(): HasMany
    {
        return $this->hasMany(Pengamatan::class);
    }
}
