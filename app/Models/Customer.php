<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_customer',
        'nama_customer',
        'alamat',
        'telepon',
        'email',
        'tipe_customer',
        'keterangan',
    ];

    /**
     * Get all sales for this customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(SalesFinishedGoods::class);
    }

    /**
     * Generate next customer code.
     */
    public static function generateCode(): string
    {
        $lastCustomer = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastCustomer ? $lastCustomer->id + 1 : 1;
        return 'CUS' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
