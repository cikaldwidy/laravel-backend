<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSetting extends Model
{
    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'batas_telat',
        'office_latitude',
        'office_longitude',
        'radius_meters',
    ];

    protected $casts = [
        'office_latitude' => 'float',
        'office_longitude' => 'float',
        'radius_meters' => 'integer',
    ];
}
