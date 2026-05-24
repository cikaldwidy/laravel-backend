<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::query()->orderBy('nama_departemen')->get();
        $positions = Position::query()
            ->with('department')
            ->withCount('employeeDetails')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('nama_jabatan', 'like', '%' . $search . '%')
                        ->orWhereHas('department', fn ($query) => $query->where('nama_departemen', 'like', '%' . $search . '%'));
                });
            })
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->input('department_id')))
            ->orderBy(
                Department::select('nama_departemen')
                    ->whereColumn('departments.id', 'positions.department_id')
            )
            ->orderBy('nama_jabatan')
            ->paginate(10)
            ->withQueryString();

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

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:positions,id'],
        ]);

        $deleted = Position::query()
            ->whereIn('id', $validated['ids'])
            ->doesntHave('employeeDetails')
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'Tidak ada jabatan yang bisa dihapus karena masih dipakai.');
        }

        return back()->with('success', $deleted . ' jabatan berhasil dihapus.');
    }
}
