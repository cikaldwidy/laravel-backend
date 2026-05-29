<?php

namespace App\Support;

class IpNetwork
{
    public static function parseList(?string $networks): array
    {
        return array_values(array_map(
            fn (array $entry) => $entry['network'],
            self::parseEntries($networks)
        ));
    }

    public static function parseEntries(?string $networks): array
    {
        if (!is_string($networks) || trim($networks) === '') {
            return [];
        }

        $decoded = json_decode($networks, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return self::normalizeEntries($decoded);
        }

        $items = preg_split('/[\s,;]+/', $networks) ?: [];

        return self::normalizeEntries(array_map(
            fn (string $item) => ['name' => '', 'network' => trim($item)],
            $items
        ));
    }

    public static function encodeEntries(array $entries): ?string
    {
        $entries = self::normalizeEntries($entries);

        if (empty($entries)) {
            return null;
        }

        return json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

    private static function normalizeEntries(array $entries): array
    {
        $normalized = [];

        foreach ($entries as $entry) {
            if (is_string($entry)) {
                $name = '';
                $network = trim($entry);
            } elseif (is_array($entry)) {
                $name = trim((string) ($entry['name'] ?? ''));
                $network = trim((string) ($entry['network'] ?? $entry['ip'] ?? $entry['cidr'] ?? ''));
            } else {
                continue;
            }

            if ($network === '') {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'network' => $network,
            ];
        }

        return $normalized;
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
