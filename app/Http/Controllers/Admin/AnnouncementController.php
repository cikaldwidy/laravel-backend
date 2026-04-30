<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Unit;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::query()
            ->with('unit')
            ->latest('tanggal_mulai')
            ->latest('created_at')
            ->get();

        $units = Unit::query()->orderBy('nama_unit')->get();

        return view('admin.announcements.index', compact('announcements', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_type' => ['required', 'in:all,unit'],
            'unit_id' => ['nullable', 'exists:units,id'],
        ]);

        Announcement::create([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_berakhir' => $validated['tanggal_berakhir'],
            'target_type' => $validated['target_type'],
            'unit_id' => $validated['target_type'] === 'unit' ? $validated['unit_id'] : null,
            'is_published' => true,
        ]);

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_berakhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_type' => ['required', 'in:all,unit'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $announcement->update([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_berakhir' => $validated['tanggal_berakhir'],
            'target_type' => $validated['target_type'],
            'unit_id' => $validated['target_type'] === 'unit' ? $validated['unit_id'] : null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        return back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
