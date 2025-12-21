<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedGoodsProductionMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_goods_production_id',
        'product_id',
        'quantity',
        'cost_per_unit',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the production header.
     */
    public function finishedGoodsProduction(): BelongsTo
    {
        return $this->belongsTo(FinishedGoodsProduction::class);
    }

    /**
     * Get the raw material product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-calculate total cost.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->total_cost = $item->quantity * $item->cost_per_unit;
        });

        static::saved(function ($item) {
            $item->finishedGoodsProduction->recalculateTotalCost();
        });

        static::deleted(function ($item) {
            $item->finishedGoodsProduction->recalculateTotalCost();
        });
    }
}
