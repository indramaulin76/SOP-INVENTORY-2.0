<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'opening_balance_id',
        'product_id',
        'jumlah_unit',
        'harga_beli',
        'harga_jual',
        'total',
    ];

    protected $casts = [
        'jumlah_unit' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the opening balance header.
     */
    public function openingBalance(): BelongsTo
    {
        return $this->belongsTo(OpeningBalance::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-calculate total before saving.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->total = $item->jumlah_unit * $item->harga_beli;
        });

        static::saved(function ($item) {
            $item->openingBalance->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->openingBalance->recalculateTotal();
        });
    }
}
