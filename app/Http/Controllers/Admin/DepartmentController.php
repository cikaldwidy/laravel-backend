<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['positions', 'employeeDetails']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where('nama_departemen', 'like', '%' . trim($request->search) . '%');
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        $query = match ($sort) {
            'name_desc'      => $query->orderByDesc('nama_departemen'),
            'positions_desc' => $query->orderByDesc('positions_count')->orderBy('nama_departemen'),
            'latest'         => $query->latest(),
            default          => $query->orderBy('nama_departemen'),
        };

        $departments = $query->paginate(10)->withQueryString();

        // Summary lebih efisien (langsung Count di DB)
        $summary = [
            'total'  => Department::count(),
            'used'   => Department::has('positions')->orHas('employeeDetails')->count(),
            'unused' => Department::doesntHave('positions')->doesntHave('employeeDetails')->count(),
        ];

        return view('admin.departments.index', compact('departments', 'summary', 'sort'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_departemen' => ['required', 'string', 'max:120', 'unique:departments,nama_departemen'],
        ], [], [
            'nama_departemen' => 'nama unit kerja/bagian',
        ]);

        Department::create($validated);

        return back()->with('success', 'Unit Kerja/Bagian berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'nama_departemen' => [
                'required',
                'string',
                'max:120',
                Rule::unique('departments', 'nama_departemen')->ignore($department->id),
            ],
        ], [], [
            'nama_departemen' => 'nama unit kerja/bagian',
        ]);

        $department->update($validated);

        return back()->with('success', 'Unit Kerja/Bagian berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->units()->exists() || $department->positions()->exists() || $department->employeeDetails()->exists()) {
            return back()->with('error', 'Unit Kerja/Bagian tidak bisa dihapus karena masih dipakai.');
        }

        $department->delete();

        return back()->with('success', 'Unit Kerja/Bagian berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (is_array($ids) && count($ids) > 0) {
            // Menghapus hanya yang tidak memiliki relasi untuk keamanan data
            Department::whereIn('id', $ids)->doesntHave('units')->doesntHave('positions')->delete();
            return back()->with('success', 'Data terpilih yang tidak memiliki relasi berhasil dihapus.');
        }
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }
}
