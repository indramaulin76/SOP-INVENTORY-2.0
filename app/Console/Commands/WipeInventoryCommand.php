<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WipeInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:wipe-inventory {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all inventory-related data (batches, transactions, products, etc.) but keep users/roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️ WARNING: This will DELETE all inventory data (Products, Batches, Transactions, Sales, Purchases, WIP). Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🗑️ Wiping inventory data...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            // Inventory core
            'inventory_batches',
            
            // Stock Opname
            'stock_opname_items',
            'stock_opnames',
            
            // Sales
            'sales_finished_goods_items',
            'sales_finished_goods',
            
            // Purchases
            'purchase_raw_material_items',
            'purchase_raw_materials',
            
            // WIP
            'wip_entry_items',
            'wip_entries',
            'usage_wip_items',
            'usage_wip',
            
            // Usage
            'usage_raw_material_items',
            'usage_raw_materials',
            'raw_material_usage_items',
            'raw_material_usages',
            
            // Finished Goods Production
            'finished_goods_production_materials',
            'finished_goods_production_items',
            'finished_goods_productions',
            
            // Opening Balance
            'opening_balance_items',
            'opening_balances',
            
            // Products (Master Data)
            'products',
            
            // Customers & Suppliers
            'customers',
            'suppliers',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->line("  ✓ Truncated: {$table}");
            } else {
                $this->line("  - Skipped (not exists): {$table}");
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('✅ Inventory data wiped successfully!');
        $this->info('💡 Run: php artisan db:seed --class=FifoLifoTestSeeder');

        return 0;
    }
}
