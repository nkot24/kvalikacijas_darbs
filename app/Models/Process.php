<?php

// app/Models/Process.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    protected $fillable = ['processa_nosaukums'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
