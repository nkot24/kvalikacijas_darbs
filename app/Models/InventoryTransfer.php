<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id','qty','from_location','to_location','created_by','accounted','accounted_at'
    ];

    protected $casts = [
        'accounted' => 'boolean',
        'accounted_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
