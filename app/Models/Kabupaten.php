<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $fillable = ['nama_kabupaten'];

    public function uppts()
    {
        return $this->hasMany(Uppt::class);
    }
}
