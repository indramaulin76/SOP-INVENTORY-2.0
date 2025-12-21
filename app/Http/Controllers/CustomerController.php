<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource (read-only view).
     */
    public function index()
    {
        $customers = Customer::latest()->get();

        return Inertia::render('LaporanDataCustomer', [
            'customers' => $customers
        ]);
    }

    /**
     * Show the form for creating a new resource (and listing).
     */
    public function create()
    {
        $customers = Customer::latest()->get();

        return Inertia::render('InputData/InputCustomer', [
            'customers' => $customers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Block karyawan from adding customers
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data customer.');
        }

        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'tipe_customer' => 'nullable|string|in:retail,reseller,corporate',
            'keterangan' => 'nullable|string|max:500',
        ]);

        // Auto-generate customer code
        $validated['kode_customer'] = Customer::generateCode();

        Customer::create($validated);

        return redirect()->back()->with('success', 'Data customer berhasil disimpan!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        // Block karyawan from editing customers
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit data customer.');
        }

        $validated = $request->validate([
            'nama_customer' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'alamat' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'tipe_customer' => 'nullable|string|in:retail,reseller,corporate',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Data customer berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        // Block karyawan from deleting customers
        if (auth()->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menghapus data customer.');
        }

        $customer->delete();
        return redirect()->back()->with('success', 'Data customer berhasil dihapus!');
    }
}
