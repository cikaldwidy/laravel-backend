<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_pulang',
    ];

    public function userShifts(): HasMany
    {
        return $this->hasMany(UserShift::class);
    }
}

