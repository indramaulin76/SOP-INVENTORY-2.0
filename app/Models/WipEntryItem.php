<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WipEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wip_entry_id',
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
     * Get the WIP entry header.
     */
    public function wipEntry(): BelongsTo
    {
        return $this->belongsTo(WipEntry::class);
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
            // Create inventory batch when WIP item is created
            InventoryBatch::create([
                'product_id' => $item->product_id,
                'batch_no' => $item->wipEntry->nomor_faktur,
                'source' => InventoryBatch::SOURCE_PURCHASE,
                'date_in' => $item->wipEntry->tanggal,
                'qty_initial' => $item->quantity,
                'qty_current' => $item->quantity,
                'price_per_unit' => $item->harga_beli,
            ]);
            
            $item->wipEntry->recalculateTotal();
        });

        static::saved(function ($item) {
            $item->wipEntry->recalculateTotal();
        });

        static::deleted(function ($item) {
            $item->wipEntry->recalculateTotal();
        });
    }
}
