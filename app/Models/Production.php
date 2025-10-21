<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Production extends Model
{
    use HasFactory;

    protected $fillable = ['order_id'];

    /**
     * Which task statuses count as "done" for a production.
     * Change this to match your final status name(s).
     */
    public const DONE_STATUSES = ['pabeigts']; // e.g. ['pabeigts', 'gatavs']

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * A production is completed if there are NO tasks with a non-done status.
     */
    public function getIsCompletedAttribute(): bool
    {
        return !$this->tasks()->whereNotIn('status', self::DONE_STATUSES)->exists();
    }

    /**
     * Scope: only productions with at least one task that is NOT done.
     * Use this to hide completed productions from your index.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('tasks', function ($q) {
            $q->whereNotIn('status', self::DONE_STATUSES);
        });
    }
    
}
