<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    // Category Name Constants
    public const BAHAN_BAKU = 'Bahan Baku';
    public const BARANG_DALAM_PROSES = 'Barang Dalam Proses';
    public const BARANG_JADI = 'Barang Jadi';

    protected $fillable = [
        'nama_kategori',
        'kode_kategori',
        'keterangan',
    ];

    /**
     * Get all products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the next auto-generated code.
     */
    public static function generateCode(): string
    {
        $lastCategory = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastCategory ? $lastCategory->id + 1 : 1;
        return 'KAT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
