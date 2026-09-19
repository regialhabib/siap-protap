<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uppt extends Model
{
    protected $guarded = [];

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pengamatans()
    {
        return $this->hasMany(Pengamatan::class);
    }
}
