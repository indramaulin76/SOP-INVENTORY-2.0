<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'unit'])
            ->latest()
            ->paginate(10);

        return response()->json($products); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'nama_kategori', 'kode_kategori')->get();
        $units = Unit::select('id', 'nama_satuan', 'singkatan')->get();

        return Inertia::render('InputBarang', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * NOTE: This is MASTER DATA only - no stock or inventory batches created here.
     * Stock should be added via "Input Barang Masuk" or "Saldo Awal" menus.
     */
    public function store(Request $request)
    {
        // Block karyawan from adding products
        if ($request->user()->isKaryawan()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menambah data barang.');
        }

        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'limit_stock' => 'required|integer|min:0',
            'harga_jual_default' => 'nullable|numeric|min:0',
        ]);

        // Auto-generate product code
        $validated['kode_barang'] = Product::generateCode($validated['category_id']);
        
        // Set defaults for pricing - will be updated via purchase transactions
        $validated['harga_beli_default'] = 0;
        $validated['harga_jual_default'] = $validated['harga_jual_default'] ?? 0;

        Product::create($validated);

        return redirect()->back()->with('success', 'Data barang berhasil disimpan! Tambahkan stok melalui menu Input Barang Masuk atau Saldo Awal.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
