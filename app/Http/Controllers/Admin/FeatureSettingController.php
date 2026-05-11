<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureSetting;
use Illuminate\Http\Request;

class FeatureSettingController extends Controller
{
    public function index()
    {
        $features = FeatureSetting::FEATURES;
        $roles = FeatureSetting::ROLES;
        $settings = FeatureSetting::matrix();
        $availableFeatures = [];

        foreach (array_keys($features) as $featureKey) {
            foreach ($roles as $role) {
                $availableFeatures[$featureKey][$role] = FeatureSetting::availableForRole($featureKey, $role);
            }
        }

        return view('admin.feature_settings.index', compact('features', 'roles', 'settings', 'availableFeatures'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
        ]);

        $input = $validated['settings'] ?? [];

        foreach (array_keys(FeatureSetting::FEATURES) as $featureKey) {
            foreach (FeatureSetting::ROLES as $role) {
                FeatureSetting::query()->updateOrCreate(
                    [
                        'feature_key' => $featureKey,
                        'role' => $role,
                    ],
                    [
                        'is_enabled' => FeatureSetting::availableForRole($featureKey, $role)
                            && isset($input[$featureKey][$role]),
                    ]
                );
            }
        }

        return back()->with('success', 'Pengaturan fitur berhasil diperbarui.');
    }
}
