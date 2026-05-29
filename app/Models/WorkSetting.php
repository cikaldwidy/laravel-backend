<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSetting extends Model
{
    public const DEFAULT_CHECKIN_EARLY_MINUTES = 60;
    public const DEFAULT_CHECKOUT_LATE_MINUTES = 180;

    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'batas_telat',
        'office_latitude',
        'office_longitude',
        'radius_meters',
        'checkin_early_minutes',
        'checkout_late_minutes',
        'attendance_network_check_enabled',
        'attendance_allowed_networks',
    ];

    protected $casts = [
        'office_latitude' => 'float',
        'office_longitude' => 'float',
        'radius_meters' => 'integer',
        'checkin_early_minutes' => 'integer',
        'checkout_late_minutes' => 'integer',
        'attendance_network_check_enabled' => 'boolean',
    ];
}
