<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Production extends Model
{
    use HasFactory;

    protected $fillable = ['order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
