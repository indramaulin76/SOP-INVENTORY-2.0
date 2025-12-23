<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_departemen',
        'nama_departemen',
        'keterangan',
    ];

    /**
     * Generate auto-incremented code for department.
     */
    public static function generateCode(): string
    {
        $lastDepartment = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = $lastDepartment ? $lastDepartment->id + 1 : 1;
        return 'DEP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
