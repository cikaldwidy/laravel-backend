<?php

namespace App\Support;

use Carbon\Carbon;

final class ShiftTime
{
    public static function isOvernight(string $jamMasuk, string $jamPulang): bool
    {
        $jamMasuk = self::normalizeTime($jamMasuk);
        $jamPulang = self::normalizeTime($jamPulang);

        // If start time is "after" end time, it crosses midnight.
        return $jamMasuk > $jamPulang;
    }

    public static function startAt(Carbon $tanggalShift, string $jamMasuk): Carbon
    {
        $jamMasuk = self::normalizeTime($jamMasuk);
        return Carbon::parse($tanggalShift->toDateString() . ' ' . $jamMasuk);
    }

    public static function endAt(Carbon $tanggalShift, string $jamMasuk, string $jamPulang): Carbon
    {
        $jamMasuk = self::normalizeTime($jamMasuk);
        $jamPulang = self::normalizeTime($jamPulang);

        $end = Carbon::parse($tanggalShift->toDateString() . ' ' . $jamPulang);
        if (self::isOvernight($jamMasuk, $jamPulang)) {
            $end->addDay();
        }
        return $end;
    }

    public static function window(
        Carbon $tanggalShift,
        string $jamMasuk,
        string $jamPulang,
        int $earlyMinutes = 0,
        int $lateMinutes = 0
    ): array {
        $start = self::startAt($tanggalShift, $jamMasuk);
        $end = self::endAt($tanggalShift, $jamMasuk, $jamPulang);

        return [
            'start' => $start,
            'end' => $end,
            'allowed_start' => $start->copy()->subMinutes($earlyMinutes),
            'allowed_end' => $end->copy()->addMinutes($lateMinutes),
        ];
    }

    private static function normalizeTime(string $time): string
    {
        // Expected "H:i" or "H:i:s"
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }
        // Fallback: let Carbon parse it; keep as H:i:s
        return Carbon::parse($time)->format('H:i:s');
    }
}

