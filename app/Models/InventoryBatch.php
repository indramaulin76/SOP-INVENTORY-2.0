<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_no',
        'source',
        'date_in',
        'qty_initial',
        'qty_current',
        'price_per_unit',
    ];

    protected $casts = [
        'date_in' => 'date',
        'qty_initial' => 'decimal:2',
        'qty_current' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
    ];

    /**
     * Source types for inventory batches.
     */
    const SOURCE_PURCHASE = 'purchase';
    const SOURCE_OPENING_BALANCE = 'opening_balance';
    const SOURCE_PRODUCTION = 'production_result';
    const SOURCE_ADJUSTMENT = 'adjustment';

    /**
     * Get the product for this batch.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for FIFO (First In, First Out) - oldest batches first.
     * Use this when consuming stock: oldest stock used first.
     */
    public function scopeFifo(Builder $query): Builder
    {
        return $query->orderBy('date_in', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Scope for LIFO (Last In, First Out) - newest batches first.
     * Use this when consuming stock: newest stock used first.
     */
    public function scopeLifo(Builder $query): Builder
    {
        return $query->orderBy('date_in', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Scope for batches with available stock.
     */
    public function scopeWithStock(Builder $query): Builder
    {
        return $query->where('qty_current', '>', 0);
    }

    /**
     * Scope for batches from a specific product.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Calculate the total value of this batch.
     */
    public function getTotalValueAttribute(): float
    {
        return $this->qty_current * $this->price_per_unit;
    }

    /**
     * Consume stock from this batch.
     * Returns the quantity actually consumed (may be less if insufficient).
     */
    public function consume(float $quantity): float
    {
        $consumed = min($quantity, $this->qty_current);
        $this->qty_current -= $consumed;
        $this->save();
        return $consumed;
    }

    /**
     * Calculate weighted average price for a product.
     */
    public static function getAveragePrice(int $productId): float
    {
        $batches = self::forProduct($productId)->withStock()->get();
        
        if ($batches->isEmpty()) {
            return 0;
        }
        
        $totalValue = $batches->sum(fn($batch) => $batch->qty_current * $batch->price_per_unit);
        $totalQty = $batches->sum('qty_current');
        
        return $totalQty > 0 ? $totalValue / $totalQty : 0;
    }

    /**
     * Consume stock using FIFO method.
     * Returns array of [batch_id => quantity_consumed] pairs.
     */
    public static function consumeFifo(int $productId, float $quantity): array
    {
        $consumed = [];
        $remaining = $quantity;
        
        $batches = self::forProduct($productId)
            ->withStock()
            ->fifo()
            ->get();
        
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            
            $taken = $batch->consume($remaining);
            if ($taken > 0) {
                $consumed[$batch->id] = [
                    'quantity' => $taken,
                    'price_per_unit' => $batch->price_per_unit,
                    'total_cost' => $taken * $batch->price_per_unit,
                ];
                $remaining -= $taken;
            }
        }
        
        return $consumed;
    }

    /**
     * Consume stock using LIFO method.
     * Returns array of [batch_id => quantity_consumed] pairs.
     */
    public static function consumeLifo(int $productId, float $quantity): array
    {
        $consumed = [];
        $remaining = $quantity;
        
        $batches = self::forProduct($productId)
            ->withStock()
            ->lifo()
            ->get();
        
        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            
            $taken = $batch->consume($remaining);
            if ($taken > 0) {
                $consumed[$batch->id] = [
                    'quantity' => $taken,
                    'price_per_unit' => $batch->price_per_unit,
                    'total_cost' => $taken * $batch->price_per_unit,
                ];
                $remaining -= $taken;
            }
        }
        
        return $consumed;
    }
}
