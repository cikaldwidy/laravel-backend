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
        $query = Department::withCount(['units', 'positions']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where('nama_departemen', 'like', '%' . trim($request->search) . '%');
        }

        // Filter Relasi
        if ($request->relasi === 'with_unit') {
            $query->has('units');
        } elseif ($request->relasi === 'no_unit') {
            $query->doesntHave('units');
        }

        // Sorting
        $sort = $request->get('sort', 'name_asc');
        $query = match ($sort) {
            'name_desc'      => $query->orderByDesc('nama_departemen'),
            'units_desc'     => $query->orderByDesc('units_count')->orderBy('nama_departemen'),
            'positions_desc' => $query->orderByDesc('positions_count')->orderBy('nama_departemen'),
            'latest'         => $query->latest(),
            default          => $query->orderBy('nama_departemen'),
        };

        $departments = $query->paginate(10)->withQueryString();

        // Summary lebih efisien (langsung Count di DB)
        $summary = [
            'total'  => Department::count(),
            'used'   => Department::has('units')->orHas('positions')->count(),
            'unused' => Department::doesntHave('units')->doesntHave('positions')->count(),
        ];

        return view('admin.departments.index', compact('departments', 'summary', 'sort'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_departemen' => ['required', 'string', 'max:120', 'unique:departments,nama_departemen'],
        ]);

        Department::create($validated);

        return back()->with('success', 'Departemen berhasil ditambahkan.');
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
        ]);

        $department->update($validated);

        return back()->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        if ($department->units()->exists() || $department->positions()->exists() || $department->employeeDetails()->exists()) {
            return back()->with('error', 'Departemen tidak bisa dihapus karena masih dipakai.');
        }

        $department->delete();

        return back()->with('success', 'Departemen berhasil dihapus.');
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
