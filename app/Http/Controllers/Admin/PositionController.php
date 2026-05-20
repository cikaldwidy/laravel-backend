<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        $departments = Department::query()->orderBy('nama_departemen')->get();
        $positions = Position::query()
            ->with('department')
            ->orderBy(
                Department::select('nama_departemen')
                    ->whereColumn('departments.id', 'positions.department_id')
            )
            ->orderBy('nama_jabatan')
            ->get();

        return view('admin.positions.index', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'nama_jabatan' => [
                'required',
                'string',
                'max:120',
                Rule::unique('positions', 'nama_jabatan')->where(
                    fn ($query) => $query->where('department_id', $request->department_id)
                ),
            ],
        ], [], [
            'department_id' => 'unit kerja/bagian',
            'nama_jabatan' => 'nama jabatan',
        ]);

        Position::create($validated);

        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'nama_jabatan' => [
                'required',
                'string',
                'max:120',
                Rule::unique('positions', 'nama_jabatan')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id))
                    ->ignore($position->id),
            ],
        ], [], [
            'department_id' => 'unit kerja/bagian',
            'nama_jabatan' => 'nama jabatan',
        ]);

        $position->update($validated);

        return back()->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        if ($position->employeeDetails()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena masih dipakai.');
        }

        $position->delete();

        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}
