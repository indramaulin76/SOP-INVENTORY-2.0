<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsageRawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'nomor_bukti',
        'nama_departemen',
        'department_id',
        'kode_referensi',
        'keterangan',
        'total_nilai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_nilai' => 'decimal:2',
    ];

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get all items in this usage.
     */
    public function items(): HasMany
    {
        return $this->hasMany(UsageRawMaterialItem::class);
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
     * Generate next reference code.
     */
    public static function generateReferenceCode(): string
    {
        $date = now()->format('Ymd');
        $lastUsage = self::whereDate('tanggal', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastUsage) {
            $lastNumber = (int) substr($lastUsage->kode_referensi, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return 'PBB-' . $date . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
