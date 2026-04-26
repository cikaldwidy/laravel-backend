<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDetail extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'departemen',
        'jabatan',
        'status_kerja',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

