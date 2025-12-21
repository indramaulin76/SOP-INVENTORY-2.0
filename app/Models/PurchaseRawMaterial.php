<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_faktur',
        'supplier_id',
        'keterangan',
        'total_nilai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    /**
     * Get the supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get all items in this purchase.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRawMaterialItem::class);
    }

    /**
     * Recalculate total from items.
     */
    public function recalculateTotal(): void
    {
        $this->total_nilai = $this->items()->sum('jumlah');
        $this->save();
    }

    /**
     * Generate next invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPurchase = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->nomor_faktur, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'PB-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
