<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Guest Routes (Unauthenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/dashboard', function () {
        return redirect()->route('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Input Data Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('input-data')->group(function () {
        Route::get('/barang', [\App\Http\Controllers\ProductController::class, 'create'])->name('input-data.barang');
        Route::get('/supplier', [\App\Http\Controllers\SupplierController::class, 'create'])->name('input-data.supplier');
        Route::get('/customer', [\App\Http\Controllers\CustomerController::class, 'create'])->name('input-data.customer');
        Route::get('/departemen', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('input-data.departemen');
        Route::get('/saldo-awal', [\App\Http\Controllers\OpeningBalanceController::class, 'create'])->name('input-data.saldo-awal');
    });

    // Master Data Resource Routes
    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
    Route::resource('opening-balances', \App\Http\Controllers\OpeningBalanceController::class);

    /*
    |--------------------------------------------------------------------------
    | Input Barang Masuk (Incoming Stock) Routes
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Input Barang Masuk (Incoming Stock) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('input-barang-masuk')->group(function () {
        // Pembelian Bahan Baku
        Route::get('/pembelian-bahan-baku', [\App\Http\Controllers\PurchaseRawMaterialController::class, 'create'])->name('barang-masuk.pembelian-bahan-baku');
        Route::post('/pembelian-bahan-baku', [\App\Http\Controllers\PurchaseRawMaterialController::class, 'store'])->name('barang-masuk.pembelian-bahan-baku.store');

        // WIP Entry
        Route::get('/barang-dalam-proses', [\App\Http\Controllers\WipEntryController::class, 'create'])->name('barang-masuk.barang-dalam-proses');
        Route::post('/barang-dalam-proses', [\App\Http\Controllers\WipEntryController::class, 'store'])->name('barang-masuk.barang-dalam-proses.store');
        Route::get('/barang-dalam-proses/riwayat', [\App\Http\Controllers\WipEntryController::class, 'index'])->name('barang-masuk.barang-dalam-proses.index');
        Route::delete('/barang-dalam-proses/{wipEntry}', [\App\Http\Controllers\WipEntryController::class, 'destroy'])->name('barang-masuk.barang-dalam-proses.destroy');

        // Produksi Barang Jadi
        Route::get('/barang-jadi', [\App\Http\Controllers\FinishedGoodsProductionController::class, 'create'])->name('barang-masuk.barang-jadi');
        Route::post('/barang-jadi', [\App\Http\Controllers\FinishedGoodsProductionController::class, 'store'])->name('barang-masuk.barang-jadi.store');
    });

    // Resource Routes for master/transactions lists
    Route::resource('purchase-raw-materials', \App\Http\Controllers\PurchaseRawMaterialController::class)->except(['create', 'store']);
    Route::resource('finished-goods-productions', \App\Http\Controllers\FinishedGoodsProductionController::class)->except(['create', 'store']);

    /*
    |--------------------------------------------------------------------------
    | Input Barang Keluar (Outgoing Stock) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('input-barang-keluar')->group(function () {
        Route::get('/penjualan-barang-jadi', [\App\Http\Controllers\SalesFinishedGoodsController::class, 'create'])->name('barang-keluar.penjualan-barang-jadi');
        Route::post('/penjualan-barang-jadi', [\App\Http\Controllers\SalesFinishedGoodsController::class, 'store'])->name('barang-keluar.penjualan-barang-jadi.store');

        Route::get('/pemakaian-bahan-baku', [\App\Http\Controllers\RawMaterialUsageController::class, 'create'])->name('barang-keluar.pemakaian-bahan-baku');
        Route::post('/pemakaian-bahan-baku', [\App\Http\Controllers\RawMaterialUsageController::class, 'store'])->name('barang-keluar.pemakaian-bahan-baku.store');

        // WIP Usage (consumption of WIP items)
        Route::get('/pemakaian-barang-dalam-proses', [\App\Http\Controllers\UsageWipController::class, 'create'])->name('barang-keluar.pemakaian-barang-dalam-proses');
        Route::post('/pemakaian-barang-dalam-proses', [\App\Http\Controllers\UsageWipController::class, 'store'])->name('barang-keluar.pemakaian-barang-dalam-proses.store');
    });

    // Resource route for sales
    Route::resource('sales-finished-goods', \App\Http\Controllers\SalesFinishedGoodsController::class)->except(['create', 'store']);

    /*
    |--------------------------------------------------------------------------
    | Laporan (Reports) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('laporan')->group(function () {
        // Laporan Data Barang (was Laporan Aset)
        Route::get('/data-barang', [\App\Http\Controllers\ReportController::class, 'dataBarang'])->name('laporan.data-barang');
        Route::delete('/data-barang/{product}', [\App\Http\Controllers\ReportController::class, 'deleteProduct'])->name('laporan.data-barang.delete');

        Route::get('/riwayat-stok', [\App\Http\Controllers\ReportController::class, 'riwayatStok'])->name('laporan.riwayat-stok');

        // Profit Reports - Pimpinan & Admin only
        Route::get('/penjualan-laba', [\App\Http\Controllers\ReportController::class, 'penjualanLaba'])
            ->middleware('role:Pimpinan,Admin')
            ->name('laporan.penjualan-laba');

        Route::get('/kartu-stok', [\App\Http\Controllers\ReportController::class, 'kartuStok'])->name('laporan.kartu-stok');

        // Stock Opname (Physical Inventory)
        Route::get('/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'create'])->name('laporan.stock-opname');
        Route::post('/stock-opname', [\App\Http\Controllers\StockOpnameController::class, 'store'])->name('laporan.stock-opname.store');
        Route::get('/stock-opname/riwayat', [\App\Http\Controllers\StockOpnameController::class, 'index'])->name('laporan.stock-opname.index');
        Route::get('/stock-opname/{stockOpname}', [\App\Http\Controllers\StockOpnameController::class, 'show'])->name('laporan.stock-opname.show');
        
        // Stock Opname Draft Management - Admin/Pimpinan only
        Route::get('/stock-opname/{stockOpname}/edit', [\App\Http\Controllers\StockOpnameController::class, 'edit'])
            ->middleware('role:Pimpinan,Admin')
            ->name('laporan.stock-opname.edit');
        Route::post('/stock-opname/{stockOpname}/finalize', [\App\Http\Controllers\StockOpnameController::class, 'finalize'])
            ->middleware('role:Pimpinan,Admin')
            ->name('laporan.stock-opname.finalize');

        Route::get('/status-barang', [\App\Http\Controllers\ReportController::class, 'statusBarang'])->name('laporan.status-barang');

        // Transaction History Edit/Delete Routes - Admin/Pimpinan only
        Route::middleware('role:Pimpinan,Admin')->group(function () {
            Route::put('/riwayat-stok/{type}/{id}', [\App\Http\Controllers\TransactionHistoryController::class, 'update'])->name('laporan.riwayat-stok.update');
            Route::delete('/riwayat-stok/{type}/{id}', [\App\Http\Controllers\TransactionHistoryController::class, 'destroy'])->name('laporan.riwayat-stok.destroy');
        });

        // Data Customer & Supplier Lists
        Route::get('/data-customer', [\App\Http\Controllers\CustomerController::class, 'index'])->name('laporan.data-customer');
        Route::get('/data-supplier', [\App\Http\Controllers\SupplierController::class, 'index'])->name('laporan.data-supplier');

        // Export Routes - Pimpinan & Admin only
        Route::middleware('role:Pimpinan,Admin')->group(function () {
            Route::get('/export/stock-history/pdf', [\App\Http\Controllers\ReportExportController::class, 'stockHistoryPdf'])->name('laporan.export.stock-history.pdf');
            Route::get('/export/stock-history/excel', [\App\Http\Controllers\ReportExportController::class, 'stockHistoryExcel'])->name('laporan.export.stock-history.excel');
            Route::get('/export/profit-loss/pdf', [\App\Http\Controllers\ReportExportController::class, 'profitLossPdf'])->name('laporan.export.profit-loss.pdf');
            Route::get('/export/profit-loss/excel', [\App\Http\Controllers\ReportExportController::class, 'profitLossExcel'])->name('laporan.export.profit-loss.excel');
            Route::get('/export/data-barang/pdf', [\App\Http\Controllers\ReportExportController::class, 'dataBarangPdf'])->name('laporan.export.data-barang.pdf');
            Route::get('/export/data-barang/excel', [\App\Http\Controllers\ReportExportController::class, 'dataBarangExcel'])->name('laporan.export.data-barang.excel');
            Route::get('/export/kartu-stok/pdf', [\App\Http\Controllers\ReportExportController::class, 'kartuStokPdf'])->name('laporan.export.kartu-stok.pdf');
            Route::get('/export/kartu-stok/excel', [\App\Http\Controllers\ReportExportController::class, 'kartuStokExcel'])->name('laporan.export.kartu-stok.excel');
            Route::get('/export/status-barang/pdf', [\App\Http\Controllers\ReportExportController::class, 'statusBarangPdf'])->name('laporan.export.status-barang.pdf');
            Route::get('/export/status-barang/excel', [\App\Http\Controllers\ReportExportController::class, 'statusBarangExcel'])->name('laporan.export.status-barang.excel');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | System Administration Routes (Pimpinan & Admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Pimpinan,Admin')->group(function () {
        Route::get('/manajemen-user', [\App\Http\Controllers\UserController::class, 'index'])->name('manajemen-user');
        Route::post('/manajemen-user', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('/manajemen-user/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::post('/manajemen-user/{user}/toggle-active', [\App\Http\Controllers\UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    // Delete user - Pimpinan only
    Route::delete('/manajemen-user/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])
        ->middleware('role:Pimpinan')
        ->name('users.destroy');


    /*
    |--------------------------------------------------------------------------
    | API-like Routes (Settings) - Pimpinan only for inventory method
    |--------------------------------------------------------------------------
    */
    Route::post('/api/settings/inventory-method', [\App\Http\Controllers\SettingsController::class, 'updateInventoryMethod'])
        ->middleware('role:Pimpinan')
        ->name('settings.inventory-method');
});
