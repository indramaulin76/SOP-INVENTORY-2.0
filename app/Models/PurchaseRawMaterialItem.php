<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRawMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_raw_material_id',
        'product_id',
        'quantity',
        'harga_beli',
        'jumlah',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the purchase header.
     */
    public function purchaseRawMaterial(): BelongsTo
    {
        return $this->belongsTo(PurchaseRawMaterial::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Auto-calculate and create inventory batch.
     */
    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->jumlah = $item->quantity * $item->harga_beli;
        });

        static::created(function ($item) {
            // Create inventory batch when purchase item is created
            InventoryBatch::create([
                'product_id' => $item->product_id,
                'batch_no' => $item->purchaseRawMaterial->nomor_faktur,
                'source' => InventoryBatch::SOURCE_PURCHASE,
                'date_in' => $item->purchaseRawMaterial->tanggal,
                'qty_initial' => $item->quantity,
                'qty_current' => $item->quantity,
                'price_per_unit' => $item->harga_beli,
            ]);
            
            $item->purchaseRawMaterial->recalculateTotal();
        });

        static::saved(function ($item) {
            $item->purchaseRawMaterial->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->purchaseRawMaterial->recalculateTotal();
        });
    }
}
