<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource (read-only view).
     */
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        
        return Inertia::render('LaporanDataSupplier', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Show the form for creating a new resource (and listing).
     */
    public function create()
    {
        $suppliers = Supplier::latest()->get(); // Get all for client-side table or paginate valid for Inertia
        
        // Let's use get() for now as displayed in table, for larger data use pagination
        return Inertia::render('InputData/InputSupplier', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Block karyawan from adding suppliers
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data supplier.');
        }

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Auto-generate supplier code
        $validated['kode_supplier'] = Supplier::generateCode();

        Supplier::create($validated);

        return redirect()->back()->with('success', 'Data supplier berhasil disimpan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        // Block karyawan from editing suppliers
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit data supplier.');
        }

        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'nama_pemilik' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $supplier->update($validated);

        return redirect()->back()->with('success', 'Data supplier berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Block karyawan from deleting suppliers
        if (auth()->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data supplier.');
        }

        $supplier->delete();
        return redirect()->back()->with('success', 'Data supplier berhasil dihapus!');
    }
}
