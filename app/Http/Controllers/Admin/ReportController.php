<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $rows = $this->buildRows($request, $tanggalMulai, $tanggalSelesai);
        $users = User::query()->where('role', 'user')->orderBy('name')->get();
        $units = Unit::query()->orderBy('nama_unit')->get();

        return view('admin.reports.index', compact('rows', 'users', 'units', 'tanggalMulai', 'tanggalSelesai'));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $rows = $this->buildRows($request, $tanggalMulai, $tanggalSelesai);
        $filename = 'laporan-presensi-' . $tanggalMulai->format('Ymd') . '-' . $tanggalSelesai->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Nama', 'Unit', 'Shift', 'Check In', 'Check Out', 'Status', 'Keterangan']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['tanggal'],
                    $row['nama'],
                    $row['unit'],
                    $row['shift'],
                    $row['check_in'],
                    $row['check_out'],
                    $row['status'],
                    $row['keterangan'],
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        [$tanggalMulai, $tanggalSelesai] = $this->resolveDateRange($request);
        $rows = $this->buildRows($request, $tanggalMulai, $tanggalSelesai);

        return view('admin.reports.pdf', compact('rows', 'tanggalMulai', 'tanggalSelesai'));
    }

    private function resolveDateRange(Request $request): array
    {
        if ($request->filled('bulan')) {
            $month = Carbon::createFromFormat('Y-m', $request->bulan);
            return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()];
        }

        $mulai = $request->filled('date_from') ? Carbon::parse($request->date_from) : today()->startOfMonth();
        $selesai = $request->filled('date_to') ? Carbon::parse($request->date_to) : today()->endOfMonth();

        return [$mulai->startOfDay(), $selesai->endOfDay()];
    }

    private function buildRows(Request $request, Carbon $tanggalMulai, Carbon $tanggalSelesai): array
    {
        $users = User::query()
            ->with(['employeeDetail.unit', 'userShifts.shift', 'leaveRequests'])
            ->where('role', 'user')
            ->when($request->filled('user_id'), fn ($query) => $query->where('id', $request->user_id))
            ->when($request->filled('unit_id'), function ($query) use ($request) {
                $query->whereHas('employeeDetail', fn ($detail) => $detail->where('unit_id', $request->unit_id));
            })
            ->get()
            ->keyBy('id');

        $presensis = Presensi::query()
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $users->keys())
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $shifts = UserShift::query()
            ->with('shift')
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()])
            ->whereIn('user_id', $users->keys())
            ->get()
            ->keyBy(fn ($item) => $item->user_id . '|' . $item->tanggal->toDateString());

        $approvedLeaves = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereIn('user_id', $users->keys())
            ->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString())
            ->whereDate('tanggal_selesai', '>=', $tanggalMulai->toDateString())
            ->get();

        $leaveMap = [];
        foreach ($approvedLeaves as $leave) {
            $cursor = $leave->tanggal_mulai->copy();
            while ($cursor->lte($leave->tanggal_selesai)) {
                $leaveMap[$leave->user_id . '|' . $cursor->toDateString()] = $leave;
                $cursor->addDay();
            }
        }

        $rows = [];
        $cursor = $tanggalMulai->copy()->startOfDay();
        while ($cursor->lte($tanggalSelesai)) {
            foreach ($users as $user) {
                $key = $user->id . '|' . $cursor->toDateString();
                $presensi = $presensis->get($key);
                $shift = $shifts->get($key)?->shift;
                $leave = $leaveMap[$key] ?? null;

                if (!$shift && !$presensi && !$leave) {
                    continue;
                }

                $status = $leave ? 'izin' : ($presensi?->status ?? 'alpha');
                $rows[] = [
                    'tanggal' => $cursor->format('Y-m-d'),
                    'nama' => $user->name,
                    'unit' => $user->employeeDetail?->unit?->nama_unit ?? ($user->employeeDetail?->departemen ?? '-'),
                    'shift' => $shift ? ($shift->nama_shift . ' (' . substr($shift->jam_masuk, 0, 5) . '-' . substr($shift->jam_pulang, 0, 5) . ')') : '-',
                    'check_in' => $presensi?->jam_masuk?->format('H:i') ?? '-',
                    'check_out' => $presensi?->jam_keluar?->format('H:i') ?? '-',
                    'status' => $status,
                    'keterangan' => $leave?->jenis_izin ?? ($presensi?->status_pulang ?? '-'),
                ];
            }
            $cursor->addDay();
        }

        return $rows;
    }
}
