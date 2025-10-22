<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'svitr_kods',
        'qty',
        'created_by',
        'accounted',
        'accounted_at',
    ];

    protected $casts = [
        'accounted' => 'boolean',
        'accounted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
