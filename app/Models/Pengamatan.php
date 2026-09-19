<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengamatan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_pengamatan' => 'date',
    ];

    public function uppt()
    {
        return $this->belongsTo(Uppt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function opt()
    {
        return $this->belongsTo(Opt::class);
    }
}
