<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedGoodsProductionWip extends Model
{
    use HasFactory;

    protected $table = 'finished_goods_production_wip';

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
     * Get the production record this WIP usage belongs to.
     */
    public function production(): BelongsTo
    {
        return $this->belongsTo(FinishedGoodsProduction::class, 'finished_goods_production_id');
    }

    /**
     * Get the WIP product used.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
