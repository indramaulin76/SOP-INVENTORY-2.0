<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageRawMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'usage_raw_material_id',
        'product_id',
        'quantity',
        'harga',
        'jumlah',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'harga' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the usage header.
     */
    public function usageRawMaterial(): BelongsTo
    {
        return $this->belongsTo(UsageRawMaterial::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-calculate total.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->jumlah = $item->quantity * $item->harga;
        });

        static::saved(function ($item) {
            $item->usageRawMaterial->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->usageRawMaterial->recalculateTotal();
        });
    }
}
