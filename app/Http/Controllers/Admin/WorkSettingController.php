<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSetting;
use Illuminate\Http\Request;

class WorkSettingController extends Controller
{
    public function edit()
    {
        $setting = WorkSetting::firstOrCreate([], [
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '16:00:00',
            'batas_telat' => 15,
            'office_latitude' => config('attendance.office_latitude'),
            'office_longitude' => config('attendance.office_longitude'),
            'radius_meters' => config('attendance.radius_meters', 100),
            'checkin_early_minutes' => WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES,
            'checkout_late_minutes' => WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES,
        ]);

        return view('admin.settings.work', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'checkin_early_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'checkout_late_minutes' => ['required', 'integer', 'min:0', 'max:480'],
        ]);

        $setting = WorkSetting::first() ?? new WorkSetting();
        $setting->fill([
            'jam_masuk' => $setting->jam_masuk ?? '08:00:00',
            'jam_pulang' => $setting->jam_pulang ?? '16:00:00',
            'batas_telat' => $setting->batas_telat ?? 15,
            'office_latitude' => $validated['office_latitude'],
            'office_longitude' => $validated['office_longitude'],
            'radius_meters' => $validated['radius_meters'],
            'checkin_early_minutes' => $validated['checkin_early_minutes'],
            'checkout_late_minutes' => $validated['checkout_late_minutes'],
        ])->save();

        return back()->with('success', 'Pengaturan lokasi presensi berhasil disimpan.');
    }
}
