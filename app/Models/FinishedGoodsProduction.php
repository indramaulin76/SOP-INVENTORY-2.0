<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinishedGoodsProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_produksi',
        'product_id',
        'quantity_produced',
        'keterangan',
        'total_cost',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'quantity_produced' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the finished product being produced.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all materials used in this production.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(FinishedGoodsProductionMaterial::class);
    }

    /**
     * Recalculate total cost from materials.
     */
    public function recalculateTotalCost(): void
    {
        $this->total_cost = $this->materials()->sum('total_cost');
        $this->save();
    }

    /**
     * Get cost per unit of finished product.
     */
    public function getCostPerUnitAttribute(): float
    {
        return $this->quantity_produced > 0 
            ? $this->total_cost / $this->quantity_produced 
            : 0;
    }

    /**
     * Generate next production number.
     */
    public static function generateProductionNumber(): string
    {
        $date = now()->format('Ymd');
        $lastProduction = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastProduction) {
            $lastNumber = (int) substr($lastProduction->nomor_produksi, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'PROD-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create inventory batch for produced goods after production is complete.
     */
    public function createInventoryBatch(): void
    {
        InventoryBatch::create([
            'product_id' => $this->product_id,
            'batch_no' => $this->nomor_produksi,
            'source' => InventoryBatch::SOURCE_PRODUCTION,
            'date_in' => $this->tanggal,
            'qty_initial' => $this->quantity_produced,
            'qty_current' => $this->quantity_produced,
            'price_per_unit' => $this->cost_per_unit,
        ]);
    }
}
