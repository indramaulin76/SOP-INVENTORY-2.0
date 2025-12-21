<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'alamat',
        'telepon',
        'email',
        'nama_pemilik',
        'keterangan',
    ];

    /**
     * Get all raw material purchases from this supplier.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseRawMaterial::class);
    }

    /**
     * Get all WIP entries from this supplier.
     */
    public function wipEntries(): HasMany
    {
        return $this->hasMany(WipEntry::class);
    }

    /**
     * Generate next supplier code.
     */
    public static function generateCode(): string
    {
        $lastSupplier = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastSupplier ? $lastSupplier->id + 1 : 1;
        return 'SUP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
