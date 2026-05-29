<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSetting;
use App\Support\IpNetwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'attendance_network_check_enabled' => false,
            'attendance_allowed_networks' => null,
        ]);

        return view('admin.settings.work', compact('setting'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'office_latitude' => ['required', 'numeric', 'between:-90,90'],
            'office_longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:5000'],
            'checkin_early_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'checkout_late_minutes' => ['required', 'integer', 'min:0', 'max:480'],
            'attendance_network_check_enabled' => ['nullable', 'boolean'],
            'attendance_networks' => ['nullable', 'array', 'max:50'],
            'attendance_networks.*.name' => ['nullable', 'string', 'max:100'],
            'attendance_networks.*.network' => ['nullable', 'string', 'max:100'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $networkCheckEnabled = $request->boolean('attendance_network_check_enabled');
            $entries = IpNetwork::parseEntries(json_encode($request->input('attendance_networks', [])));
            $networks = array_column($entries, 'network');

            if ($networkCheckEnabled && empty($networks)) {
                $validator->errors()->add(
                    'attendance_networks',
                    'Isi minimal satu IP atau subnet jika pembatasan jaringan diaktifkan.'
                );
            }

            foreach ($networks as $network) {
                if (!IpNetwork::isValid($network)) {
                    $validator->errors()->add(
                        'attendance_networks',
                        "Format IP/subnet tidak valid: {$network}."
                    );
                }
            }
        });

        $validated = $validator->validate();
        $allowedNetworks = IpNetwork::encodeEntries($validated['attendance_networks'] ?? []);

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
            'attendance_network_check_enabled' => $request->boolean('attendance_network_check_enabled'),
            'attendance_allowed_networks' => $allowedNetworks !== '' ? $allowedNetworks : null,
        ])->save();

        return back()->with('success', 'Pengaturan presensi berhasil disimpan.');
    }
}
