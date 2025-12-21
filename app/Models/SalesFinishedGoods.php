<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesFinishedGoods extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_bukti',
        'customer_id',
        'keterangan',
        'total_nilai',
        'total_cogs',
        'total_profit',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'total_profit' => 'decimal:2',
    ];

    /**
     * Get the customer (optional).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all items in this sale.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesFinishedGoodsItem::class);
    }

    /**
     * Recalculate totals from items.
     */
    public function recalculateTotals(): void
    {
        $this->total_nilai = $this->items()->sum('jumlah');
        $this->total_cogs = $this->items()->sum('total_cogs');
        $this->total_profit = $this->total_nilai - $this->total_cogs;
        $this->save();
    }

    /**
     * Get profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        return $this->total_nilai > 0 
            ? ($this->total_profit / $this->total_nilai) * 100 
            : 0;
    }

    /**
     * Generate next receipt number.
     */
    public static function generateReceiptNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->nomor_bukti, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'SLS-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
