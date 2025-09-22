<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderList extends Model
{
    use SoftDeletes;

    protected $table = 'order_list';

    protected $fillable = [
        'name',
        'quantity',
        'photo_path',
        'created_by',
        'supplier_name',
        'ordered_at',
        'expected_at',
        'arrived_at',
        'status',
    ];

    protected $casts = [
        'ordered_at'  => 'date',
        'expected_at' => 'date',
        'arrived_at'  => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Auto-status: ordered -> pasūtīts, arrived -> saņemts
    public function setOrderedAtAttribute($value)
    {
        $this->attributes['ordered_at'] = $value;
        if (!empty($value) && empty($this->attributes['arrived_at'])) {
            $this->attributes['status'] = 'pasūtīts';
        }
    }

    public function setArrivedAtAttribute($value)
    {
        $this->attributes['arrived_at'] = $value;
        if (!empty($value)) {
            $this->attributes['status'] = 'saņemts';
        }
    }

    // Scopes
    public function scopeActive($q)    { return $q->where('status', '!=', 'saņemts'); }
    public function scopeCompleted($q) { return $q->where('status', 'saņemts'); }
}
