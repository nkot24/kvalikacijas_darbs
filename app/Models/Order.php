<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasutijuma_numurs',
        'datums',
        'client_id',
        'klients',
        'products_id',
        'produkts',
        'daudzums',
        'izpildes_datums',
        'prioritāte',
        'statuss',
        'piezimes',
    ];

    protected $casts = [
        'datums' => 'date',
        'izpildes_datums' => 'date',
    ];

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            if (empty($order->pasutijuma_numurs)) {
                $order->pasutijuma_numurs = now()->year . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
                $order->saveQuietly();
            }
        });
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('statuss', '!=', 'pabeigts');
    }

    public function scopeCompleted($query)
    {
        return $query->where('statuss', 'pabeigts');
    }

    // Relationships

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function production()
    {
        return $this->hasOne(Production::class);
    }

    // Business logic

    public function deleteProductionData(): void
    {
        if (! $this->production) {
            return;
        }

        foreach ($this->production->tasks as $task) {
            foreach ($task->files as $file) {
                Storage::disk('public')->delete($file->path);
                $file->delete();
            }
            $task->assignedUsers()?->detach();
            $task->delete();
        }

        Storage::disk('public')->deleteDirectory("process_files/production_{$this->production->id}");
        $this->production->delete();
    }
}
