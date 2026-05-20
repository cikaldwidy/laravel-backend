<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.departments.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'nama_unit' => ['required', 'string', 'max:120', 'unique:units,nama_unit'],
        ], [], [
            'department_id' => 'unit kerja/bagian',
            'nama_unit' => 'nama unit',
        ]);

        Unit::create($validated);

        return back()->with('success', 'Unit berhasil ditambahkan.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'nama_unit' => [
                'required',
                'string',
                'max:120',
                Rule::unique('units', 'nama_unit')->ignore($unit->id),
            ],
        ], [], [
            'department_id' => 'unit kerja/bagian',
            'nama_unit' => 'nama unit',
        ]);

        $unit->update($validated);

        return back()->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->employeeDetails()->exists()) {
            return back()->with('error', 'Unit tidak bisa dihapus karena masih dipakai.');
        }

        $unit->delete();

        return back()->with('success', 'Unit berhasil dihapus.');
    }
}
