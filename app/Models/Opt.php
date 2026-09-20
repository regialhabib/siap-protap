<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opt extends Model
{
    protected $fillable = ['nama_opt'];

    public function pengamatans()
    {
        return $this->hasMany(Pengamatan::class);
    }
}
