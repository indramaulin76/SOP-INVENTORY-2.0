<?php

namespace App\Console\Commands;

use App\Models\SalesFinishedGoodsItem;
use App\Models\InventoryBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateSalesHpp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:recalculate-hpp 
                            {--dry-run : Preview changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate HPP (COGS) for existing sales items based on inventory batches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('=== Recalculating HPP for Sales Items ===');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        // Get all sales items with 0 or null harga_pokok
        $items = SalesFinishedGoodsItem::with(['product', 'salesFinishedGoods'])
            ->where(function ($q) {
                $q->where('harga_pokok', 0)
                  ->orWhereNull('harga_pokok');
            })
            ->get();

        $this->info("Found {$items->count()} items with zero HPP");

        if ($items->isEmpty()) {
            $this->info('No items need recalculation.');
            return 0;
        }

        $updated = 0;
        
        foreach ($items as $item) {
            $product = $item->product;
            
            if (!$product) {
                $this->warn("Item #{$item->id}: Product not found, skipping");
                continue;
            }

            // Get weighted average cost from inventory batches
            $batches = InventoryBatch::where('product_id', $item->product_id)
                ->get();

            if ($batches->isEmpty()) {
                $this->warn("Item #{$item->id} ({$product->nama_barang}): No inventory batches found");
                continue;
            }

            // Calculate weighted average cost
            $totalQty = $batches->sum('qty_initial');
            $totalValue = $batches->sum(function ($batch) {
                return $batch->qty_initial * $batch->price_per_unit;
            });

            $averageCost = $totalQty > 0 ? $totalValue / $totalQty : 0;

            if ($averageCost == 0) {
                // Try using product's default purchase price
                $averageCost = $product->harga_beli_default ?? 0;
            }

            if ($averageCost == 0) {
                $this->warn("Item #{$item->id} ({$product->nama_barang}): Cannot determine cost, skipping");
                continue;
            }

            $oldHpp = $item->harga_pokok;
            $newHpp = $averageCost;
            $newTotalCogs = $item->quantity * $newHpp;
            $newProfit = $item->jumlah - $newTotalCogs;

            $this->info(sprintf(
                "Item #%d: %s | Qty: %s | HPP: %s -> %s | COGS: %s | Profit: %s",
                $item->id,
                $product->nama_barang,
                number_format($item->quantity, 2),
                number_format($oldHpp, 0),
                number_format($newHpp, 0),
                number_format($newTotalCogs, 0),
                number_format($newProfit, 0)
            ));

            if (!$dryRun) {
                // Update directly to avoid triggering model events that might cause issues
                DB::table('sales_finished_goods_items')
                    ->where('id', $item->id)
                    ->update([
                        'harga_pokok' => $newHpp,
                        'total_cogs' => $newTotalCogs,
                        'profit' => $newProfit,
                        'updated_at' => now(),
                    ]);

                $updated++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("DRY RUN: {$items->count()} items would be updated");
        } else {
            $this->info("Successfully updated {$updated} items");

            // Recalculate totals for affected sales
            $this->info('Recalculating sales totals...');
            
            $salesIds = $items->pluck('sales_finished_goods_id')->unique();
            
            foreach ($salesIds as $salesId) {
                $sales = \App\Models\SalesFinishedGoods::find($salesId);
                if ($sales) {
                    $sales->recalculateTotals();
                    $this->info("Updated totals for sale: {$sales->nomor_bukti}");
                }
            }
        }

        $this->info('=== Done ===');
        return 0;
    }
}
