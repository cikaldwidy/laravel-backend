<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::query()
            ->with(['unit', 'users'])
            ->where(function ($query) {
                $query->where('target_type', '!=', 'users')
                    ->orWhere(function ($manualUserAnnouncements) {
                        $manualUserAnnouncements->where('target_type', 'users')
                            ->where('judul', 'not like', '%Tukar Shift%');
                    });
            })
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->get();

        $units = Department::query()->orderBy('nama_departemen')->get();
        $users = User::query()
            ->where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.announcements.index', compact('announcements', 'units', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_type' => ['required', 'in:all,unit,users'],
            'unit_id' => ['nullable', 'required_if:target_type,unit', 'exists:departments,id'],
            'user_ids' => ['nullable', 'required_if:target_type,users', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $targetType = $validated['target_type'] === 'unit' ? 'users' : $validated['target_type'];

        $announcement = Announcement::create([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_berakhir' => $validated['tanggal_berakhir'],
            'target_type' => $targetType,
            'unit_id' => null,
            'is_published' => true,
        ]);

        if ($validated['target_type'] === 'unit') {
            $departmentUserIds = User::query()
                ->where('role', 'user')
                ->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $validated['unit_id']))
                ->pluck('id')
                ->all();
            $announcement->users()->sync($departmentUserIds);
        } elseif ($validated['target_type'] === 'users') {
            $announcement->users()->sync($validated['user_ids'] ?? []);
        }

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_type' => ['required', 'in:all,unit,users'],
            'unit_id' => ['nullable', 'required_if:target_type,unit', 'exists:departments,id'],
            'user_ids' => ['nullable', 'required_if:target_type,users', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $targetType = $validated['target_type'] === 'unit' ? 'users' : $validated['target_type'];

        $announcement->update([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_berakhir' => $validated['tanggal_berakhir'],
            'target_type' => $targetType,
            'unit_id' => null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        if ($validated['target_type'] === 'unit') {
            $departmentUserIds = User::query()
                ->where('role', 'user')
                ->whereHas('employeeDetail', fn ($detail) => $detail->where('department_id', $validated['unit_id']))
                ->pluck('id')
                ->all();
            $announcement->users()->sync($departmentUserIds);
        } else {
            $announcement->users()->sync($validated['target_type'] === 'users' ? ($validated['user_ids'] ?? []) : []);
        }

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
