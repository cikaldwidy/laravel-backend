<?php

namespace App\Http\Controllers;

use App\Models\FeatureSetting;
use Illuminate\Http\Request;

class FeaturePageController extends Controller
{
    public function user(string $featureKey)
    {
        abort_unless(isset(FeatureSetting::FEATURES[$featureKey]), 404);

        if (in_array($featureKey, ['sakit', 'cuti'], true)) {
            return redirect()->route('leave_requests.create', ['jenis_izin' => $featureKey]);
        }

        return view('feature-placeholder', [
            'title' => FeatureSetting::FEATURES[$featureKey]['label'],
            'roleLabel' => 'User',
            'backUrl' => route('dashboard'),
        ]);
    }

    public function admin(Request $request, string $featureKey)
    {
        abort_unless(isset(FeatureSetting::FEATURES[$featureKey]), 404);

        if (in_array($featureKey, ['sakit', 'cuti'], true)) {
            return redirect()->route('admin.leave_requests.index', ['jenis_izin' => $featureKey]);
        }

        return view('admin.feature_pages.placeholder', [
            'title' => FeatureSetting::FEATURES[$featureKey]['label'],
        ]);
    }
}
