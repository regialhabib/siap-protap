<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $fillable = ['uppt_id', 'nama_kecamatan'];

    public function uppt()
    {
        return $this->belongsTo(Uppt::class);
    }
}
