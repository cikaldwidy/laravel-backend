<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    public const ADMIN_HIDDEN_FEATURES = ['istirahat', 'slip_gaji'];

    public const FEATURES = [
        'sakit' => [
            'label' => 'Sakit',
            'icon' => 'fa-user-injured',
        ],
        'cuti' => [
            'label' => 'Cuti',
            'icon' => 'fa-plane-departure',
        ],
        'istirahat' => [
            'label' => 'Istirahat',
            'icon' => 'fa-mug-hot',
        ],
        'lembur' => [
            'label' => 'Lembur',
            'icon' => 'fa-clock',
        ],
        'slip_gaji' => [
            'label' => 'Slip Gaji',
            'icon' => 'fa-wallet',
        ],
    ];

    public const ROLES = ['user', 'admin'];

    protected $fillable = [
        'feature_key',
        'role',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public static function enabled(string $featureKey, string $role): bool
    {
        if (!static::availableForRole($featureKey, $role)) {
            return false;
        }

        return (bool) static::query()
            ->where('feature_key', $featureKey)
            ->where('role', $role)
            ->value('is_enabled');
    }

    public static function availableForRole(string $featureKey, string $role): bool
    {
        return $role !== 'admin' || !in_array($featureKey, self::ADMIN_HIDDEN_FEATURES, true);
    }

    public static function featureKeysForRole(string $role): array
    {
        return array_values(array_filter(
            array_keys(self::FEATURES),
            fn (string $featureKey) => static::availableForRole($featureKey, $role)
        ));
    }

    public static function matrix(): array
    {
        $settings = static::query()
            ->get()
            ->keyBy(fn (FeatureSetting $setting) => $setting->feature_key . ':' . $setting->role);

        $matrix = [];

        foreach (array_keys(self::FEATURES) as $featureKey) {
            foreach (self::ROLES as $role) {
                $matrix[$featureKey][$role] = static::availableForRole($featureKey, $role)
                    && (bool) ($settings->get($featureKey . ':' . $role)?->is_enabled ?? false);
            }
        }

        return $matrix;
    }
}
