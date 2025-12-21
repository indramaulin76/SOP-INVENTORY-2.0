<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Update inventory valuation method (FIFO/LIFO/AVERAGE)
     */
    public function updateInventoryMethod(Request $request)
    {
        $validated = $request->validate([
            'method' => 'required|in:FIFO,LIFO,AVERAGE',
        ]);

        Setting::set(
            'inventory_method',
            $validated['method'],
            'string',
            'Metode penilaian persediaan: FIFO, LIFO, atau AVERAGE'
        );

        // Return redirect back for Inertia compatibility
        return redirect()->back();
    }

    /**
     * Get all settings (for admin page)
     */
    public function index()
    {
        return response()->json([
            'inventory_method' => Setting::get('inventory_method', 'FIFO'),
            'company_name' => Setting::get('company_name', 'SAE Bakery'),
        ]);
    }
}
