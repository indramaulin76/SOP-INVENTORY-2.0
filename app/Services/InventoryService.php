<?php

namespace App\Services;

use App\Models\InventoryBatch;
use App\Models\Setting;

/**
 * =========================================================================
 * INVENTORY SERVICE - MOTOR UTAMA KALKULASI HPP
 * =========================================================================
 * 
 * Service ini adalah otak utama untuk semua kalkulasi inventory.
 * Mendukung 3 metode akuntansi yang bisa di-switch real-time:
 * - FIFO: Batch tertua dikonsumsi duluan (cocok untuk barang perishable)
 * - LIFO: Batch terbaru dikonsumsi duluan (jarang dipakai, tapi ada use-case)
 * - AVERAGE: Semua batch dihitung rata-rata (paling simple, cocok untuk UKM)
 * 
 * @author Sae Bakery Dev Team
 * @since 1.0.0
 */
class InventoryService
{
    /**
     * Konsumsi inventory sesuai metode yang aktif di Setting
     * 
     * Fungsi utama ini cek dulu metode apa yang dipilih Pimpinan,
     * lalu diarahkan ke logika yang sesuai (FIFO/LIFO/AVERAGE).
     * 
     * @param int $productId ID produk yang dikonsumsi
     * @param float $quantity Jumlah yang dibutuhkan
     * @return array Format: ['total_cost', 'average_cost', 'consumed_batches']
     * @throws \Exception Stok tidak cukup
     */
    public function consumeInventory(int $productId, float $quantity): array
    {
        $method = Setting::get('inventory_method', 'FIFO');
        
        return match ($method) {
            'LIFO' => $this->consumeLIFO($productId, $quantity),
            'AVERAGE' => $this->consumeAverage($productId, $quantity),
            default => $this->consumeFIFO($productId, $quantity),
        };
    }

    /**
     * FIFO - First In First Out
     * 
     * Logikanya: ambil stok dari batch paling lama (date_in paling kecil).
     * Ini standar industri untuk barang yang punya masa kadaluarsa.
     */
    protected function consumeFIFO(int $productId, float $quantity): array
    {
        $batches = InventoryBatch::where('product_id', $productId)
            ->where('qty_current', '>', 0)
            ->orderBy('date_in', 'asc') // Tertua duluan
            ->orderBy('id', 'asc')
            ->get();

        return $this->processBatchConsumption($batches, $quantity, $productId);
    }

    /**
     * LIFO - Last In First Out
     * 
     * Kebalikan FIFO: ambil stok dari batch terbaru.
     * Jarang dipakai, tapi berguna untuk kasus tertentu.
     */
    protected function consumeLIFO(int $productId, float $quantity): array
    {
        $batches = InventoryBatch::where('product_id', $productId)
            ->where('qty_current', '>', 0)
            ->orderBy('date_in', 'desc') // Terbaru duluan
            ->orderBy('id', 'desc')
            ->get();

        return $this->processBatchConsumption($batches, $quantity, $productId);
    }

    /**
     * AVERAGE - Weighted Average Cost
     * 
     * Hitung rata-rata tertimbang dari semua batch tersedia.
     * Rumus: Total Nilai / Total Qty = Harga Rata-Rata
     * 
     * Metode paling simple, cocok untuk bisnis yang bahan bakunya
     * harganya relatif stabil dan tidak perlu tracing per batch.
     */
    protected function consumeAverage(int $productId, float $quantity): array
    {
        $batches = InventoryBatch::where('product_id', $productId)
            ->where('qty_current', '>', 0)
            ->get();

        // Hitung total nilai dan qty semua batch
        $totalQty = $batches->sum('qty_current');
        $totalValue = $batches->sum(function ($batch) {
            return $batch->qty_current * $batch->price_per_unit;
        });

        // Validasi stok cukup
        if ($totalQty < $quantity) {
            throw new \Exception("Stok tidak mencukupi untuk produk ID: {$productId}. Tersedia: {$totalQty}, Dibutuhkan: {$quantity}");
        }

        // Hitung harga rata-rata
        $averageCost = $totalQty > 0 ? $totalValue / $totalQty : 0;

        // Konsumsi dari batch tertua (urutan tidak penting untuk average, tapi tetap konsisten)
        $remainingQty = $quantity;
        $consumedBatches = [];

        foreach ($batches->sortBy('date_in') as $batch) {
            if ($remainingQty <= 0) break;

            $qtyToConsume = min($remainingQty, $batch->qty_current);
            
            $batch->qty_current -= $qtyToConsume;
            $batch->save();

            $consumedBatches[] = [
                'batch_id' => $batch->id,
                'qty' => $qtyToConsume,
                'cost_per_unit' => $averageCost, // Pakai harga rata-rata, bukan harga batch
            ];

            $remainingQty -= $qtyToConsume;
        }

        return [
            'total_cost' => $quantity * $averageCost,
            'average_cost' => $averageCost,
            'consumed_batches' => $consumedBatches,
        ];
    }

    /**
     * Proses konsumsi batch (logic internal untuk FIFO/LIFO)
     * 
     * Loop semua batch sesuai urutan, kurangi stok satu per satu
     * sampai kebutuhan terpenuhi. Catat biaya dari masing-masing batch.
     */
    protected function processBatchConsumption($batches, float $quantity, int $productId): array
    {
        $remainingQty = $quantity;
        $totalCost = 0;
        $consumedBatches = [];

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $qtyToConsume = min($remainingQty, $batch->qty_current);
            $costToAdd = $qtyToConsume * $batch->price_per_unit;
            
            // Kurangi stok di batch ini
            $batch->qty_current -= $qtyToConsume;
            $batch->save();

            $consumedBatches[] = [
                'batch_id' => $batch->id,
                'qty' => $qtyToConsume,
                'cost_per_unit' => $batch->price_per_unit, // Pakai harga asli batch
            ];

            $totalCost += $costToAdd;
            $remainingQty -= $qtyToConsume;
        }

        // Kalau masih kurang, berarti stok tidak cukup
        if ($remainingQty > 0) {
            throw new \Exception("Stok tidak mencukupi untuk produk ID: {$productId}. Kurang: {$remainingQty}");
        }

        // Hitung rata-rata untuk keperluan reporting
        $averageCost = $quantity > 0 ? $totalCost / $quantity : 0;

        return [
            'total_cost' => $totalCost,
            'average_cost' => $averageCost,
            'consumed_batches' => $consumedBatches,
        ];
    }

    /**
     * Cek stok saat ini untuk produk tertentu
     */
    public function getCurrentStock(int $productId): float
    {
        return InventoryBatch::where('product_id', $productId)
            ->sum('qty_current');
    }

    /**
     * Hitung valuasi inventory (total nilai aset)
     * 
     * Dipakai untuk dashboard dan laporan aset.
     */
    public function getInventoryValue(int $productId): array
    {
        $batches = InventoryBatch::where('product_id', $productId)
            ->where('qty_current', '>', 0)
            ->get();

        $totalQty = $batches->sum('qty_current');
        $totalValue = $batches->sum(function ($batch) {
            return $batch->qty_current * $batch->price_per_unit;
        });

        return [
            'quantity' => $totalQty,
            'total_value' => $totalValue,
            'average_cost' => $totalQty > 0 ? $totalValue / $totalQty : 0,
        ];
    }

    /**
     * Ambil harga rata-rata per unit
     */
    public function getAveragePrice(int $productId): float
    {
        $valuation = $this->getInventoryValue($productId);
        return $valuation['average_cost'];
    }

    /**
     * Kembalikan stok ke inventory (untuk rollback/delete transaksi)
     * 
     * Kalau ada batch dengan harga sama, tambahkan ke batch itu.
     * Kalau tidak ada, buat batch baru dengan prefix RST- (Restored).
     */
    public function restoreInventory(int $productId, float $quantity, float $costPerUnit, string $source = 'restored'): void
    {
        // Cari batch dengan harga yang sama
        $existingBatch = InventoryBatch::where('product_id', $productId)
            ->where('price_per_unit', $costPerUnit)
            ->where('qty_current', '>', 0)
            ->orderBy('date_in', 'asc')
            ->first();

        if ($existingBatch) {
            // Tambahkan ke batch yang sudah ada
            $existingBatch->qty_current += $quantity;
            $existingBatch->save();
        } else {
            // Buat batch baru untuk restoration
            InventoryBatch::create([
                'product_id' => $productId,
                'batch_no' => 'RST-' . now()->format('Ymd-His'),
                'source' => $source,
                'date_in' => now()->toDateString(),
                'qty_initial' => $quantity,
                'qty_current' => $quantity,
                'price_per_unit' => $costPerUnit,
            ]);
        }
    }
}
