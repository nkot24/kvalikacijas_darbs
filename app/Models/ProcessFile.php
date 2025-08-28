<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// app/Models/ProcessFile.php
class ProcessFile extends Model
{
    protected $fillable = [
        'process_id', 'task_id', 'uploaded_by', 'original_name', 'path', 'mime', 'size',
    ];

    public function process(): BelongsTo {
        return $this->belongsTo(Process::class);
    }

    public function task(): BelongsTo {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string {
        return asset('storage/'.$this->path);
    }
}

