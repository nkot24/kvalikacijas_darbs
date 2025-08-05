<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Booted method to automatically set pasutijuma_numurs after creation.
     */
    protected static function booted()
    {
        static::created(function ($order) {
            if (empty($order->pasutijuma_numurs)) {
                $order->pasutijuma_numurs = now()->year . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
                $order->save();
            }
        });
    }

    // Optional: Define relationships
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

}
