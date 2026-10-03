<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kabupaten extends Model
{
    protected $fillable = ['nama_kabupaten'];

    public function uppts(): HasMany
    {
        return $this->hasMany(Uppt::class);
    }
}
