<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkLogController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now('Europe/Riga')->toDateString();

        $log = WorkLog::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return view('work.index', compact('log', 'today'));
    }

    public function startWork()
    {
        $user = Auth::user();
        $today = Carbon::now('Europe/Riga')->toDateString();
        $now = WorkLog::roundTimeTo10Min(Carbon::now('Europe/Riga'));

        WorkLog::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['start_time' => $now]
        );

        return back()->with('success', 'Darbs sācies ' . $now);
    }

    public function endWork()
    {
        $user = Auth::user();
        $today = Carbon::now('Europe/Riga')->toDateString();
        $now = WorkLog::roundTimeTo10Min(Carbon::now('Europe/Riga'));

        $log = WorkLog::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($log && $log->start_time) {
            $start = Carbon::parse($log->start_time);
            $end = Carbon::parse($now);
            $hours = $end->floatDiffInHours($start);

            $log->update([
                'end_time' => $now,
                'hours_worked' => $hours,
            ]);
        }

        return back()->with('success', 'Darbs beigts ' . $now);
    }

    public function workHoursView(Request $request)
    {
        $users = User::all();
        $logs = collect();
        $totalHours = 0;
        $lunchMinutes = (int) ($request->lunch_minutes ?? 0);

        if ($request->filled('user_id') && $request->filled('from') && $request->filled('to')) {
            $logs = WorkLog::where('user_id', $request->user_id)
                ->whereBetween('date', [$request->from, $request->to])
                ->orderBy('date', 'asc')
                ->get();

            foreach ($logs as $log) {
                $log->adjusted_hours = 0;

                if (!empty($log->start_time) && !empty($log->end_time)) {
                    try {
                        // ✅ Ensure date is plain string (not Carbon object)
                        $date = $log->date instanceof Carbon
                            ? $log->date->format('Y-m-d')
                            : (string) $log->date;

                        // ✅ Parse as local times and normalize for calculation
                        $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$log->start_time}", 'Europe/Riga')
                            ->setTimezone('UTC');
                        $end = Carbon::createFromFormat('Y-m-d H:i:s', "{$date} {$log->end_time}", 'Europe/Riga')
                            ->setTimezone('UTC');

                        if ($end->lessThan($start)) {
                            $end->addDay();
                        }

                        $minutesWorked = abs($end->diffInMinutes($start));
                        $hours = $minutesWorked / 60;
                        $adjusted = max(0, $hours - ($lunchMinutes / 60));

                        $log->adjusted_hours = round($adjusted, 2);
                        $totalHours += $log->adjusted_hours;
                    } catch (\Exception $e) {
                        $log->adjusted_hours = 0;
                    }
                }
            }
        }

        return view('work.work_hours', compact('users', 'logs', 'totalHours', 'lunchMinutes'));
    }


}
