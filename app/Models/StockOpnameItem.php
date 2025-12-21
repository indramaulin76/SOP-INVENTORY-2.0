<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'qty_system',
        'qty_physical',
        'qty_difference',
        'price_per_unit',
        'value_difference',
        'notes',
    ];

    protected $casts = [
        'qty_system' => 'decimal:2',
        'qty_physical' => 'decimal:2',
        'qty_difference' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'value_difference' => 'decimal:2',
    ];

    /**
     * Get the stock opname header.
     */
    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-calculate difference and value.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->qty_difference = $item->qty_physical - $item->qty_system;
            $item->value_difference = $item->qty_difference * $item->price_per_unit;
        });
    }
}
