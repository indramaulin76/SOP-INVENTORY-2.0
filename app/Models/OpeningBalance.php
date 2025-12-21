<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpeningBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'total_nilai',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    /**
     * Get all items in this opening balance.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OpeningBalanceItem::class);
    }

    /**
     * Recalculate total from items.
     */
    public function recalculateTotal(): void
    {
        $this->total_nilai = $this->items()->sum('total');
        $this->save();
    }
}
