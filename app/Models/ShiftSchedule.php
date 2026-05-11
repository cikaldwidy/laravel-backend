<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'shift_code',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i:s',
        'jam_pulang' => 'datetime:H:i:s',
    ];

    public function getNamaShiftAttribute(): string
    {
        if ($this->status === 'libur') {
            return 'Libur';
        }

        return 'Shift ' . $this->jam_masuk?->format('H:i') . ' - ' . $this->jam_pulang?->format('H:i');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class, 'shift_id');
    }

    public function targetSwaps(): HasMany
    {
        return $this->hasMany(ShiftSwap::class, 'target_shift_id');
    }
}
