<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryBatch;
use App\Models\UsageRawMaterialItem;
use App\Models\WipEntryItem;
use App\Models\UsageWipItem;
use App\Models\SalesFinishedGoodsItem;

class TransactionHistoryController extends Controller
{
    /**
     * Update transaction (Safe Mode - only notes, date, reference)
     */
    public function update(Request $request, string $type, int $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($type, $id, $validated) {
                switch ($type) {
                    case 'batch':
                        $batch = InventoryBatch::findOrFail($id);
                        // InventoryBatch doesn't have notes field, only batch_no and date_in
                        if (isset($validated['transaction_date'])) {
                            $batch->date_in = $validated['transaction_date'];
                        }
                        if (isset($validated['reference_number'])) {
                            $batch->batch_no = $validated['reference_number'];
                        }
                        $batch->save();
                        break;

                    case 'usage':
                        $item = UsageRawMaterialItem::findOrFail($id);
                        $parent = $item->usageRawMaterial;
                        if ($parent) {
                            if (isset($validated['transaction_date'])) {
                                $parent->tanggal = $validated['transaction_date'];
                            }
                            if (isset($validated['notes'])) {
                                $parent->keterangan = $validated['notes'];
                            }
                            if (isset($validated['reference_number'])) {
                                $parent->kode_referensi = $validated['reference_number'];
                            }
                            $parent->save();
                        }
                        break;

                    case 'wip':
                        $item = WipEntryItem::findOrFail($id);
                        $parent = $item->wipEntry;
                        if ($parent) {
                            if (isset($validated['transaction_date'])) {
                                $parent->tanggal = $validated['transaction_date'];
                            }
                            if (isset($validated['notes'])) {
                                $parent->keterangan = $validated['notes'];
                            }
                            if (isset($validated['reference_number'])) {
                                $parent->nomor_faktur = $validated['reference_number'];
                            }
                            $parent->save();
                        }
                        break;

                    case 'usage_wip':
                        $item = UsageWipItem::findOrFail($id);
                        $parent = $item->usageWip;
                        if ($parent) {
                            if (isset($validated['transaction_date'])) {
                                $parent->tanggal = $validated['transaction_date'];
                            }
                            if (isset($validated['notes'])) {
                                $parent->keterangan = $validated['notes'];
                            }
                            if (isset($validated['reference_number'])) {
                                $parent->kode_referensi = $validated['reference_number'];
                            }
                            $parent->save();
                        }
                        break;

                    case 'sales':
                        $item = SalesFinishedGoodsItem::findOrFail($id);
                        $parent = $item->salesFinishedGoods;
                        if ($parent) {
                            if (isset($validated['transaction_date'])) {
                                $parent->tanggal = $validated['transaction_date'];
                            }
                            if (isset($validated['notes'])) {
                                $parent->keterangan = $validated['notes'];
                            }
                            if (isset($validated['reference_number'])) {
                                $parent->nomor_bukti = $validated['reference_number'];
                            }
                            $parent->save();
                        }
                        break;

                    default:
                        throw new \Exception('Tipe transaksi tidak valid');
                }
            });

            return redirect()->back()->with('success', 'Transaksi berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Delete transaction with stock reversal
     */
    public function destroy(string $type, int $id)
    {
        try {
            DB::transaction(function () use ($type, $id) {
                switch ($type) {
                    case 'batch':
                        $this->deleteBatch($id);
                        break;

                    case 'usage':
                        $this->deleteUsage($id);
                        break;

                    case 'wip':
                        $this->deleteWip($id);
                        break;

                    case 'usage_wip':
                        $this->deleteUsageWip($id);
                        break;

                    case 'sales':
                        $this->deleteSales($id);
                        break;

                    default:
                        throw new \Exception('Tipe transaksi tidak valid');
                }
            });

            return redirect()->back()->with('success', 'Transaksi berhasil dihapus dan stok telah disesuaikan');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    /**
     * Delete InventoryBatch (INBOUND)
     * Effect: REDUCE stock
     */
    private function deleteBatch(int $id): void
    {
        $batch = InventoryBatch::findOrFail($id);
        
        // Check if batch has been used (qty_current < qty_initial)
        $usedQty = $batch->qty_initial - $batch->qty_current;
        if ($usedQty > 0) {
            throw new \Exception(
                "Tidak dapat menghapus batch ini karena sudah digunakan. " .
                "Qty terpakai: {$usedQty}. Hapus transaksi pemakaian/penjualan terlebih dahulu."
            );
        }

        // Safe to delete - no stock was used from this batch
        $batch->delete();
    }

    /**
     * Delete UsageRawMaterialItem (OUTBOUND - Usage)
     * Effect: ADD stock back
     */
    private function deleteUsage(int $id): void
    {
        $item = UsageRawMaterialItem::with('usageRawMaterial')->findOrFail($id);
        $parent = $item->usageRawMaterial;
        $productId = $item->product_id;
        $quantity = $item->quantity;

        // Restore stock to the most recent batch with available capacity
        $this->restoreStockToBatch($productId, $quantity);

        // Delete the item
        $item->delete();

        // If parent has no more items, delete parent too
        if ($parent && $parent->items()->count() === 0) {
            $parent->delete();
        }
    }

    /**
     * Delete WipEntryItem (INBOUND - WIP)
     * Effect: REDUCE WIP stock
     */
    private function deleteWip(int $id): void
    {
        $item = WipEntryItem::with('wipEntry')->findOrFail($id);
        $parent = $item->wipEntry;

        // Check if there's a related batch and if it's been used
        $relatedBatch = InventoryBatch::where('product_id', $item->product_id)
            ->where('source', 'wip_entry')
            ->where('qty_initial', $item->quantity)
            ->first();

        if ($relatedBatch) {
            $usedQty = $relatedBatch->qty_initial - $relatedBatch->qty_current;
            if ($usedQty > 0) {
                throw new \Exception(
                    "Tidak dapat menghapus WIP ini karena sudah digunakan. " .
                    "Qty terpakai: {$usedQty}."
                );
            }
            $relatedBatch->delete();
        }

        // Delete the item
        $item->delete();

        // If parent has no more items, delete parent too
        if ($parent && $parent->items()->count() === 0) {
            $parent->delete();
        }
    }

    /**
     * Delete UsageWipItem (OUTBOUND - WIP Usage)
     * Effect: ADD WIP stock back
     */
    private function deleteUsageWip(int $id): void
    {
        $item = UsageWipItem::with('usageWip')->findOrFail($id);
        $parent = $item->usageWip;
        $productId = $item->product_id;
        $quantity = $item->quantity;

        // Restore WIP stock
        $this->restoreStockToBatch($productId, $quantity);

        // Delete the item
        $item->delete();

        // If parent has no more items, delete parent too
        if ($parent && $parent->items()->count() === 0) {
            $parent->delete();
        }
    }

    /**
     * Delete SalesFinishedGoodsItem (OUTBOUND - Sales)
     * Effect: ADD stock back
     */
    private function deleteSales(int $id): void
    {
        $item = SalesFinishedGoodsItem::with('salesFinishedGoods')->findOrFail($id);
        $parent = $item->salesFinishedGoods;
        $productId = $item->product_id;
        $quantity = $item->quantity;

        // Restore stock
        $this->restoreStockToBatch($productId, $quantity);

        // Delete the item
        $item->delete();

        // If parent has no more items, delete parent too
        if ($parent && $parent->items()->count() === 0) {
            $parent->delete();
        }
    }

    /**
     * Restore stock to the most recent batch
     * Creates new batch if no suitable batch found
     */
    private function restoreStockToBatch(int $productId, float $quantity): void
    {
        // Find the most recent batch for this product
        $latestBatch = InventoryBatch::where('product_id', $productId)
            ->orderBy('date_in', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($latestBatch) {
            // Add quantity back to this batch
            $latestBatch->qty_current += $quantity;
            $latestBatch->save();
        } else {
            // Create a new adjustment batch
            InventoryBatch::create([
                'product_id' => $productId,
                'source' => 'adjustment',
                'batch_no' => 'ADJ-RESTORE-' . now()->format('YmdHis'),
                'date_in' => now(),
                'qty_initial' => $quantity,
                'qty_current' => $quantity,
                'price_per_unit' => 0, // Unknown price for restored stock
            ]);
        }
    }
}
