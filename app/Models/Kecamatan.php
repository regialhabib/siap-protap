<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kecamatan extends Model
{
    protected $fillable = ['uppt_id', 'nama_kecamatan'];

    public function uppt(): BelongsTo
    {
        return $this->belongsTo(Uppt::class);
    }
}
