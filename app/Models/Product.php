<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'category_id',
        'unit_id',
        'limit_stock',
        'harga_beli_default',
        'harga_jual_default',
    ];

    protected $casts = [
        'limit_stock' => 'integer',
        'harga_beli_default' => 'decimal:2',
        'harga_jual_default' => 'decimal:2',
    ];

    /**
     * Get the category this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the unit this product uses.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get all inventory batches for this product.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(InventoryBatch::class);
    }

    /**
     * Get the current total stock (sum of all batch qty_current).
     */
    public function getCurrentStockAttribute(): float
    {
        return $this->inventoryBatches()->sum('qty_current');
    }

    /**
     * Check if stock is below limit.
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->limit_stock;
    }

    /**
     * Generate next product code based on category.
     */
    public static function generateCode(int $categoryId): string
    {
        $category = Category::find($categoryId);
        $prefix = match($category?->kode_kategori) {
            'BB' => 'BB',  // Bahan Baku
            'BJ' => 'BJ',  // Barang Jadi
            'BDP' => 'BDP', // Barang Dalam Proses
            'KMS' => 'KMS', // Kemasan
            default => 'PRD',
        };
        
        $lastProduct = self::withTrashed()
            ->where('kode_barang', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
            
        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->kode_barang, strlen($prefix) + 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
