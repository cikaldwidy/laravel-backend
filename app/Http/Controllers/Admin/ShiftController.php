<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::query()
            ->orderBy('nama_shift')
            ->paginate(10);

        return view('admin.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:100'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
        ]);

        Shift::create([
            'nama_shift' => $validated['nama_shift'],
            'jam_masuk' => $validated['jam_masuk'] . ':00',
            'jam_pulang' => $validated['jam_pulang'] . ':00',
        ]);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'nama_shift' => ['required', 'string', 'max:100'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
        ]);

        $shift->update([
            'nama_shift' => $validated['nama_shift'],
            'jam_masuk' => $validated['jam_masuk'] . ':00',
            'jam_pulang' => $validated['jam_pulang'] . ':00',
        ]);

        return redirect()->route('admin.shifts.index')->with('success', 'Shift berhasil diperbarui.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('admin.shifts.index')->with('success', 'Shift berhasil dihapus.');
    }
}
