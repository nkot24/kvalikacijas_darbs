<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkLogController extends Controller
{
    /**
     * Start/End page for the authenticated user.
     */
    public function index()
    {
        $user  = Auth::user();
        $now   = Carbon::now('Europe/Riga');
        $today = $now->toDateString();

        // Today's log
        $log = WorkLog::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // Get all this month's logs for the user
        $monthLogs = WorkLog::where('user_id', $user->id)
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get(['hours_worked']);

        // Sum absolute hours_worked (removes minus)
        $monthHours = $monthLogs->sum(function ($log) {
            return abs((float) $log->hours_worked);
        });

        $monthHours = round($monthHours, 2);

        return view('work.index', compact('log', 'today', 'monthHours'));
    }

    /**
     * Start work now.
     */
    public function startWork()
    {
        $user  = Auth::user();
        $today = Carbon::now('Europe/Riga')->toDateString();
        $now   = Carbon::now('Europe/Riga')->format('H:i:s');

        WorkLog::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['start_time' => $now]
        );

        return back()->with('success', 'Darbs sācies ' . $now);
    }

    /**
     * End work now; store lunch & breaks from modal.
     */
    public function endWork(Request $request)
    {
        $validated = $request->validate([
            'lunch_minutes' => 'nullable|integer|min:0|max:600',
            'break_count'   => 'nullable|integer|min:0|max:48',
        ]);

        $user  = Auth::user();
        $today = Carbon::now('Europe/Riga')->toDateString();
        $now   = Carbon::now('Europe/Riga')->format('H:i:s');

        $log = WorkLog::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($log && $log->start_time) {
            $start = Carbon::parse($log->start_time, 'Europe/Riga');
            $end   = Carbon::parse($now, 'Europe/Riga');

            $hours = $end->floatDiffInHours($start);

            $log->update([
                'end_time'       => $now,
                'hours_worked'   => round($hours, 2),
                'lunch_minutes'  => (int)($validated['lunch_minutes'] ?? 0),
                'break_count'    => (int)($validated['break_count'] ?? 0),
            ]);
        }

        return back()->with('success', 'Darbs beigts ' . $now);
    }

    /**
     * Work hours report (filters by user and date range).
     * Uses per-row lunch_minutes & break_count (1 break = 10 minutes).
     */
    public function workHoursView(Request $request)
    {
        $users = User::all();
        $logs = collect();
        $totalHours = 0.0;

        if ($request->filled('user_id') && $request->filled('from') && $request->filled('to')) {
            if ($request->user_id === 'all') {
                $logs = WorkLog::whereBetween('date', [$request->from, $request->to])
                    ->orderBy('user_id')
                    ->orderBy('date', 'asc')
                    ->get();
            } else {
                $logs = WorkLog::where('user_id', $request->user_id)
                    ->whereBetween('date', [$request->from, $request->to])
                    ->orderBy('date', 'asc')
                    ->get();
            }

            foreach ($logs as $log) {
                $log->adjusted_hours = 0.0;

                if (!empty($log->start_time) && !empty($log->end_time)) {
                    try {
                        $dateStr = $log->date instanceof Carbon
                            ? $log->date->format('Y-m-d')
                            : (string) $log->date;

                        $start = Carbon::createFromFormat('Y-m-d H:i:s', "{$dateStr} {$log->start_time}", 'Europe/Riga');
                        $end   = Carbon::createFromFormat('Y-m-d H:i:s', "{$dateStr} {$log->end_time}", 'Europe/Riga');

                        if ($end->lessThan($start)) {
                            $end->addDay();
                        }

                        $minutesWorked = abs($end->diffInMinutes($start));
                        $lunchMinutes  = (int)($log->lunch_minutes ?? 0);
                        $breakMinutes  = (int)($log->break_count ?? 0) * 10;

                        $deduct = max(0, $lunchMinutes + $breakMinutes);
                        $netMinutes = max(0, $minutesWorked - $deduct);
                        $hours = $netMinutes / 60;

                        $log->adjusted_hours = round($hours, 2);
                        $totalHours += $log->adjusted_hours;
                    } catch (\Exception $e) {
                        $log->adjusted_hours = 0.0;
                    }
                }
            }

            if ($request->user_id === 'all') {
                $userTotals = $logs->groupBy('user_id')->map(fn($userLogs) => $userLogs->sum('adjusted_hours'));
                return view('work.work_hours', compact('users', 'logs', 'totalHours', 'userTotals'));
            }
        }

        return view('work.work_hours', compact('users', 'logs', 'totalHours'));
    }

    /**
     * Inline update of start_time or end_time (HH:MM:SS).
     * Keeps hours_worked as raw diff of start/end.
     */
    public function updateTime(Request $request, $id)
    {
        $validated = $request->validate([
            'column' => 'required|in:start_time,end_time',
            'value'  => 'required|date_format:H:i:s',
        ]);

        $log = WorkLog::findOrFail($id);
        $log->{$validated['column']} = $validated['value'];
        $log->save();

        if ($log->start_time && $log->end_time) {
            $start = Carbon::createFromFormat('H:i:s', $log->start_time, 'Europe/Riga');
            $end   = Carbon::createFromFormat('H:i:s', $log->end_time, 'Europe/Riga');
            if ($end->lessThan($start)) $end->addDay();

            $log->hours_worked = round($end->floatDiffInHours($start), 2);
            $log->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Generic inline update for lunch_minutes & break_count too.
     */
    public function updateField(Request $request, $id)
    {
        $request->validate([
            'column' => 'required|in:start_time,end_time,lunch_minutes,break_count',
            'value'  => 'required',
        ]);

        $log = WorkLog::findOrFail($id);
        $column = $request->input('column');
        $value  = $request->input('value');

        switch ($column) {
            case 'start_time':
            case 'end_time':
                $request->validate(['value' => 'date_format:H:i:s']);
                $log->{$column} = $value;
                break;

            case 'lunch_minutes':
                $request->validate(['value' => 'integer|min:0|max:600']);
                $log->lunch_minutes = (int)$value;
                break;

            case 'break_count':
                $request->validate(['value' => 'integer|min:0|max:48']);
                $log->break_count = (int)$value;
                break;
        }

        $log->save();

        if ($log->start_time && $log->end_time) {
            $start = Carbon::createFromFormat('H:i:s', $log->start_time, 'Europe/Riga');
            $end   = Carbon::createFromFormat('H:i:s', $log->end_time, 'Europe/Riga');
            if ($end->lessThan($start)) $end->addDay();
            $log->hours_worked = round($end->floatDiffInHours($start), 2);
            $log->save();
        }

        return response()->json(['success' => true]);
    }
}
