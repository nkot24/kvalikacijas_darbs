<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'hours_worked',
    ];

    // ❗ Only cast date — keep start/end as plain strings to avoid timezone bugs
    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional rounding helper
    public static function roundTimeTo10Min($time)
    {
        $carbon = Carbon::parse($time, 'Europe/Riga');
        $minutes = $carbon->minute;

        $roundedMinutes = match (true) {
            $minutes < 5 => 0,
            $minutes < 15 => 10,
            $minutes < 25 => 20,
            $minutes < 35 => 30,
            $minutes < 45 => 40,
            $minutes < 55 => 50,
            default => 0, // for 55–59, reset to 0 and add an hour
        };

        if ($minutes >= 55) {
            $carbon->addHour();
        }

        return $carbon->setMinute($roundedMinutes)->setSecond(0)->format('H:i:s');
    }
}
