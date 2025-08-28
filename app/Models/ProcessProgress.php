<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessProgress extends Model
{
    use HasFactory;

    /**
     * Explicit table name (plural).
     */
    protected $table = 'process_progresses';

    /**
     * Mass assignable fields.
     */
    protected $fillable = [
        'task_id',
        'process_id',
        'user_id',
        'status',
        'spent_time',   // minutes
        'comment',
    ];

    /**
     * Casts.
     */
    protected $casts = [
        'spent_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Touch the parent process timestamp when this model updates (optional but useful).
     * Ensure Process model has $timestamps = true (default).
     */
    protected $touches = ['process'];

    /**
     * Status constants (keep in sync with validation).
     */
    public const STATUS_PLANNED   = 'ieplānots';
    public const STATUS_RUNNING   = 'procesā';
    public const STATUS_PARTIAL   = 'daļeji_pabeigts';
    public const STATUS_DONE      = 'pabeigts';

    /**
     * Allowed statuses list.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNED,
            self::STATUS_RUNNING,
            self::STATUS_PARTIAL,
            self::STATUS_DONE,
        ];
    }

    /**
     * Relationships.
     */
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes.
     */
    public function scopeFinalized($query)
    {
        return $query->whereIn('status', [self::STATUS_PARTIAL, self::STATUS_DONE]);
    }

    public function scopeForProcess($query, int $processId)
    {
        return $query->where('process_id', $processId);
    }

    public function scopeByUser($query, ?int $userId)
    {
        return $query->when($userId, fn($q) => $q->where('user_id', $userId));
    }

    /**
     * Helpers.
     */
    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function hasTime(): bool
    {
        return !is_null($this->spent_time) && $this->spent_time > 0;
    }

    /**
     * Accessors.
     */
    public function getSpentTimeHumanAttribute(): string
    {
        if (!$this->hasTime()) {
            return '—';
        }

        $m = (int) $this->spent_time;
        $h = intdiv($m, 60);
        $r = $m % 60;

        if ($h > 0) {
            return trim($h . 'h ' . ($r ? $r . 'm' : ''));
        }
        return $m . 'm';
    }
}
