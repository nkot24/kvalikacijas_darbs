<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_id',
        'process_id',
        'user_id',
        'status',
        'done_amount',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class);
    }
    public function order()
    {
        return $this->production->order();
    }


    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function workLogs()
    {
        return $this->hasMany(TaskWorkLog::class);
    }
}

