<?php

namespace App\Http\Middleware;

use App\Models\FeatureSetting;
use Closure;
use Illuminate\Http\Request;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        if (str_starts_with($featureKey, '{') && str_ends_with($featureKey, '}')) {
            $featureKey = (string) $request->route(trim($featureKey, '{}'));
        }

        $role = $request->user()?->role;

        if (!$role || !FeatureSetting::enabled($featureKey, $role)) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini.');
        }

        return $next($request);
    }
}
