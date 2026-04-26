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
        ]);

        return view('admin.settings.work', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
            'batas_telat' => ['required', 'integer', 'min:0', 'max:240'],
        ], [
            'jam_pulang.after' => 'Jam pulang harus lebih besar dari jam masuk.',
        ]);

        $setting = WorkSetting::first() ?? new WorkSetting();
        $setting->fill([
            'jam_masuk' => $validated['jam_masuk'] . ':00',
            'jam_pulang' => $validated['jam_pulang'] . ':00',
            'batas_telat' => $validated['batas_telat'],
        ])->save();

        return back()->with('success', 'Pengaturan jam kerja berhasil disimpan.');
    }
}
