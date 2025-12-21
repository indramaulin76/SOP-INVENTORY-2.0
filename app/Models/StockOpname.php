<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_opname',
        'keterangan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Get the user who created this opname.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all items in this opname.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    /**
     * Generate next opname number.
     */
    public static function generateOpnameNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOpname = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastOpname) {
            $lastNumber = (int) substr($lastOpname->nomor_opname, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'SO-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total surplus value.
     */
    public function getTotalSurplusAttribute(): float
    {
        return $this->items()
            ->where('qty_difference', '>', 0)
            ->sum('value_difference');
    }

    /**
     * Calculate total loss value.
     */
    public function getTotalLossAttribute(): float
    {
        return abs($this->items()
            ->where('qty_difference', '<', 0)
            ->sum('value_difference'));
    }
}
