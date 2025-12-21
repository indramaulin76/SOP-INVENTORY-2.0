<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesFinishedGoodsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_finished_goods_id',
        'product_id',
        'quantity',
        'harga_jual',
        'harga_pokok',
        'jumlah',
        'total_cogs',
        'profit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'harga_pokok' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    /**
     * Get the sales header.
     */
    public function salesFinishedGoods(): BelongsTo
    {
        return $this->belongsTo(SalesFinishedGoods::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get profit margin for this item.
     */
    public function getProfitMarginAttribute(): float
    {
        return $this->jumlah > 0 
            ? ($this->profit / $this->jumlah) * 100 
            : 0;
    }

    /**
     * Auto-calculate totals.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->jumlah = $item->quantity * $item->harga_jual;
            $item->total_cogs = $item->quantity * $item->harga_pokok;
            $item->profit = $item->jumlah - $item->total_cogs;
        });

        static::saved(function ($item) {
            $item->salesFinishedGoods->recalculateTotals();
        });

        static::deleted(function ($item) {
            $item->salesFinishedGoods->recalculateTotals();
        });
    }
}
