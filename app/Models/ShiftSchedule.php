<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftSchedule extends Model
{
    public const SHIFT_TYPE_OPTIONS = [
        'P' => 'Pagi',
        'S' => 'Sore',
        'M' => 'Malam',
        'O' => 'Libur',
    ];

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
        $code = self::normalizeShiftTypeCode($this->shift_code);

        if ($this->status === 'libur' || $code === 'O') {
            return 'O';
        }

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
        return self::SHIFT_TYPE_OPTIONS[$this->shift_type_code] ?? 'Shift';
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

    public function scopeOfShiftType(Builder $query, ?string $shiftType): Builder
    {
        $shiftType = self::normalizeShiftTypeCode($shiftType);

        if (!array_key_exists($shiftType, self::SHIFT_TYPE_OPTIONS)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($shiftType) {
            if ($shiftType === 'O') {
                $q->where('status', 'libur')
                    ->orWhereIn('shift_code', self::shiftCodeAliases('O'));

                return;
            }

            $q->where(function (Builder $codeQuery) use ($shiftType) {
                $codeQuery->where('status', 'aktif')
                    ->whereIn('shift_code', self::shiftCodeAliases($shiftType));
            })->orWhere(function (Builder $timeQuery) use ($shiftType) {
                $timeQuery->where('status', 'aktif')
                    ->where(function (Builder $emptyCode) {
                        $emptyCode->whereNull('shift_code')->orWhere('shift_code', '');
                    });

                match ($shiftType) {
                    'P' => $timeQuery->whereTime('jam_masuk', '>=', '05:00:00')
                        ->whereTime('jam_masuk', '<', '12:00:00'),
                    'S' => $timeQuery->whereTime('jam_masuk', '>=', '12:00:00')
                        ->whereTime('jam_masuk', '<', '18:00:00'),
                    'M' => $timeQuery->where(function (Builder $time) {
                        $time->whereTime('jam_masuk', '>=', '18:00:00')
                            ->orWhereTime('jam_masuk', '<', '05:00:00');
                    }),
                    default => null,
                };
            });
        });
    }

    public static function normalizeShiftTypeCode(?string $value): string
    {
        return match (strtolower(trim((string) $value))) {
            'p', 'pagi' => 'P',
            's', 'sore' => 'S',
            'm', 'malam' => 'M',
            'o', 'off', 'l', 'libur' => 'O',
            default => strtoupper(trim((string) $value)),
        };
    }

    private static function shiftCodeAliases(string $shiftType): array
    {
        return match ($shiftType) {
            'P' => ['P', 'p', 'Pagi', 'pagi'],
            'S' => ['S', 's', 'Sore', 'sore'],
            'M' => ['M', 'm', 'Malam', 'malam'],
            'O' => ['O', 'o', 'Off', 'off', 'L', 'l', 'Libur', 'libur'],
            default => [$shiftType],
        };
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
