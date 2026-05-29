<?php

namespace App\Support;

class IpNetwork
{
    public static function parseList(?string $networks): array
    {
        if (!is_string($networks) || trim($networks) === '') {
            return [];
        }

        $items = preg_split('/[\s,;]+/', $networks) ?: [];

        return array_values(array_filter(array_map('trim', $items), fn (string $item) => $item !== ''));
    }

    public static function contains(array $networks, string $ip): bool
    {
        foreach ($networks as $network) {
            if (self::matches($ip, $network)) {
                return true;
            }
        }

        return false;
    }

    public static function isValid(string $network): bool
    {
        if (filter_var($network, FILTER_VALIDATE_IP)) {
            return true;
        }

        if (!str_contains($network, '/')) {
            return false;
        }

        [$ip, $prefix] = explode('/', $network, 2);
        if (!filter_var($ip, FILTER_VALIDATE_IP) || !ctype_digit($prefix)) {
            return false;
        }

        $maxPrefix = str_contains($ip, ':') ? 128 : 32;

        return (int) $prefix >= 0 && (int) $prefix <= $maxPrefix;
    }

    private static function matches(string $ip, string $network): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP) || !self::isValid($network)) {
            return false;
        }

        if (!str_contains($network, '/')) {
            return inet_pton($ip) === inet_pton($network);
        }

        [$networkIp, $prefix] = explode('/', $network, 2);
        $ipBinary = inet_pton($ip);
        $networkBinary = inet_pton($networkIp);

        if ($ipBinary === false || $networkBinary === false || strlen($ipBinary) !== strlen($networkBinary)) {
            return false;
        }

        $prefix = (int) $prefix;
        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        if ($fullBytes > 0 && substr($ipBinary, 0, $fullBytes) !== substr($networkBinary, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;

        return (ord($ipBinary[$fullBytes]) & $mask) === (ord($networkBinary[$fullBytes]) & $mask);
    }
}
