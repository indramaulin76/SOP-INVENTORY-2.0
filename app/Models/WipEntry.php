<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WipEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_faktur',
        'supplier_id',
        'department_id',
        'keterangan',
        'total_nilai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    /**
     * Get the supplier (optional).
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all items in this WIP entry.
     */
    public function items(): HasMany
    {
        return $this->hasMany(WipEntryItem::class);
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
        $lastEntry = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->nomor_faktur, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'WIP-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
