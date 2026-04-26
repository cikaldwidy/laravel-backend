<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSetting extends Model
{
    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'batas_telat'
    ];
}