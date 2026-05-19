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

    public function getShiftTypeCodeAttribute(): string
    {
        if ($this->status === 'libur' || strtoupper((string) $this->shift_code) === 'O') {
            return 'O';
        }

        $code = strtoupper((string) $this->shift_code);
        if (in_array($code, ['P', 'S', 'M'], true)) {
            return $code;
        }

        $hour = (int) ($this->jam_masuk?->format('H') ?? 0);

        if ($hour >= 5 && $hour < 12) {
            return 'P';
        }

        if ($hour >= 12 && $hour < 18) {
            return 'S';
        }

        return 'M';
    }

    public function getShiftTypeLabelAttribute(): string
    {
        return [
            'P' => 'Pagi',
            'S' => 'Sore',
            'M' => 'Malam',
            'O' => 'Libur',
        ][$this->shift_type_code] ?? 'Shift';
    }

    public function getShiftTypeBadgeClassAttribute(): string
    {
        return [
            'P' => 'bg-blue-100 text-blue-700',
            'S' => 'bg-amber-100 text-amber-700',
            'M' => 'bg-slate-800 text-white',
            'O' => 'bg-red-100 text-red-700',
        ][$this->shift_type_code] ?? 'bg-slate-100 text-slate-700';
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
