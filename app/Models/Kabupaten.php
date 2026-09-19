<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    protected $guarded = [];

    public function uppts()
    {
        return $this->hasMany(Uppt::class);
    }
}
