<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        $departments = Department::orderBy('nama_departemen')->get();

        return Inertia::render('InputDepartemen', [
            'departments' => $departments,
            'newCode' => Department::generateCode(),
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_departemen' => 'required|string|max:20|unique:departments,kode_departemen',
            'nama_departemen' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        Department::create($validated);

        return redirect()->back()->with('success', 'Departemen berhasil ditambahkan!');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'kode_departemen' => 'required|string|max:20|unique:departments,kode_departemen,' . $department->id,
            'nama_departemen' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $department->update($validated);

        return redirect()->back()->with('success', 'Departemen berhasil diperbarui!');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->back()->with('success', 'Departemen berhasil dihapus!');
    }
}
