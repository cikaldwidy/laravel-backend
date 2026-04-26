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
        ]);

        return view('admin.settings.work', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'batas_telat' => ['required', 'integer', 'min:0', 'max:240'],
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
        ], [
            'jam_pulang.after' => 'Jam pulang harus lebih besar dari jam masuk.',
        ]);

        $setting = WorkSetting::first() ?? new WorkSetting();
        $setting->fill([
            'jam_masuk' => $validated['jam_masuk'] . ':00',
            'jam_pulang' => $validated['jam_pulang'] . ':00',
            'batas_telat' => $validated['batas_telat'],
            'office_latitude' => $validated['office_latitude'],
            'office_longitude' => $validated['office_longitude'],
            'radius_meters' => $validated['radius_meters'],
        ])->save();

        return back()->with('success', 'Pengaturan jam kerja dan GPS berhasil disimpan.');
    }
}
