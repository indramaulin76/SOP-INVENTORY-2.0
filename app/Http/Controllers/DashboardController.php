<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesFinishedGoods;
use App\Services\InventoryService;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real statistics.
     */
    public function index()
    {
        // Get real product count
        $totalProducts = Product::count();
        
        // Get low stock count
        $lowStockCount = Product::withSum('inventoryBatches', 'qty_current')
            ->get()
            ->filter(function ($product) {
                $currentStock = $product->inventory_batches_sum_qty_current ?? 0;
                return $currentStock <= $product->limit_stock;
            })
            ->count();
        
        // Get real sales and profit from SalesFinishedGoods
        $totalSales = SalesFinishedGoods::sum('total_nilai');
        $totalProfit = SalesFinishedGoods::sum('total_profit');
        
        // Get today's sales
        $todaySales = SalesFinishedGoods::whereDate('tanggal', Carbon::today())->sum('total_nilai');
        $todayProfit = SalesFinishedGoods::whereDate('tanggal', Carbon::today())->sum('total_profit');
        
        // Get this month's sales
        $monthSales = SalesFinishedGoods::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total_nilai');
        
        // Generate sales trend data for the last 30 days
        $salesTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dailySales = SalesFinishedGoods::whereDate('tanggal', $date)->sum('total_nilai');
            $salesTrend[] = [
                'date' => $date->format('d M'),
                'value' => (float) $dailySales,
            ];
        }
        
        // Generate weekly trend (last 8 weeks)
        $weeklyTrend = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            $weeklySales = SalesFinishedGoods::whereBetween('tanggal', [$weekStart, $weekEnd])->sum('total_nilai');
            $weeklyTrend[] = [
                'date' => 'W' . $weekStart->weekOfYear,
                'value' => (float) $weeklySales,
            ];
        }
        
        // Generate monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthlySales = SalesFinishedGoods::whereMonth('tanggal', $monthDate->month)
                ->whereYear('tanggal', $monthDate->year)
                ->sum('total_nilai');
            $monthlyTrend[] = [
                'date' => $monthDate->format('M Y'),
                'value' => (float) $monthlySales,
            ];
        }
        
        // Top selling products
        $topProducts = \App\Models\SalesFinishedGoodsItem::with('product')
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(jumlah) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->nama_barang ?? 'Unknown',
                    'qty' => $item->total_qty,
                    'revenue' => $item->total_revenue,
                ];
            });
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'total_sales' => (float) $totalSales,
                'total_profit' => (float) $totalProfit,
                'today_sales' => (float) $todaySales,
                'today_profit' => (float) $todayProfit,
                'month_sales' => (float) $monthSales,
            ],
            'chartData' => [
                'daily' => $salesTrend,
                'weekly' => $weeklyTrend,
                'monthly' => $monthlyTrend,
            ],
            'topProducts' => $topProducts,
        ]);
    }
}
