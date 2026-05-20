<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\ShiftSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class PresensiDinasController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();
        $users = User::query()
            ->with('employeeDetail.department')
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        $schedules = ShiftSchedule::query()
            ->whereDate('tanggal', $tanggal)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $presensis = Presensi::query()
            ->whereDate('tanggal', $tanggal)
            ->whereIn('user_id', $users->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return view('admin.presensi_dinas.index', compact('tanggal', 'users', 'schedules', 'presensis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'presensi' => ['required', 'array'],
            'presensi.*.status' => ['required', 'in:hadir,terlambat,izin,sakit,alpha'],
            'presensi.*.jam_masuk' => ['nullable', 'date_format:H:i'],
            'presensi.*.jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        $employeeIds = User::query()
            ->where('role', 'user')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        foreach ($validated['presensi'] as $userId => $payload) {
            if (!in_array((string) $userId, $employeeIds, true)) {
                continue;
            }

            Presensi::updateOrCreate(
                [
                    'user_id' => (int) $userId,
                    'tanggal' => $validated['tanggal'],
                ],
                [
                    'jam_masuk' => $payload['jam_masuk'] ?? null,
                    'jam_keluar' => $payload['jam_pulang'] ?? null,
                    'status' => $payload['status'],
                ]
            );
        }

        return redirect()
            ->route('presensi-dinas.index', ['tanggal' => $validated['tanggal']])
            ->with('success', 'Presensi berhasil disimpan.');
    }
}
