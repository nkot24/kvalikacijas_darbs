<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Production;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Tasks assigned directly to the user
        $personalTasks = Task::with(['process', 'production.order'])
            ->where('user_id', $user->id)
            ->get();

        // Shared tasks (user_id is null), but the current user is part of the process
        $sharedTasks = Task::with(['process', 'production.order'])
            ->whereNull('user_id')
            ->get()
            ->filter(function ($task) use ($user) {
                return $task->process->users->contains('id', $user->id);
            });

        // Combine both task types
        $allTasks = $personalTasks->concat($sharedTasks);

        $currentTasks = collect();
        $futureTasks = collect();

        $tasksByProduction = $allTasks->groupBy('production_id');

        foreach ($tasksByProduction as $groupedTasks) {
            $productionId = $groupedTasks->first()->production_id;
            $allTasksForProduction = Task::where('production_id', $productionId)->get();

            $processIds = $allTasksForProduction->pluck('process_id')->unique()->sort()->values();
            $unlockedProcessId = null;

            foreach ($processIds as $processId) {
                $tasksInThisProcess = $allTasksForProduction->where('process_id', $processId);
                $allDone = $tasksInThisProcess->every(fn($t) => $t->status === 'pabeigts');
                if (!$allDone) {
                    $unlockedProcessId = $processId;
                    break;
                }
            }

            foreach ($groupedTasks as $task) {
                if ((int)$task->process_id === (int)$unlockedProcessId) {
                    $currentTasks->push($task);
                } elseif ($task->status !== 'pabeigts' && (int)$task->process_id !== (int)$unlockedProcessId) {
                    $futureTasks->push($task);
                }
            }
        }

        return view('tasks.index', [
            'currentTasks' => $currentTasks,
            'futureTasks' => $futureTasks,
        ]);
    }

    public function show(Task $task)
    {
        $user = auth()->user();

        if (
            $task->user_id !== null && $task->user_id !== $user->id ||
            $task->user_id === null && !$task->process->users->contains($user)
        ) {
            abort(403, 'Unauthorized action.');
        }

        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $user = auth()->user();

        if (
            $task->user_id !== null && $task->user_id !== $user->id ||
            $task->user_id === null && !$task->process->users->contains($user)
        ) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:nav uzsākts,daļēji pabeigts,pabeigts',
            'done_amount' => 'nullable|integer|min:0',
        ]);

        // Claim shared task
        if ($task->user_id === null) {
            $task->user_id = $user->id;
        }

        // Increment done_amount instead of overwriting
        if ($validated['status'] === 'pabeigts') {
            $newDone = $task->production->order->daudzums;
        } elseif ($validated['status'] === 'daļēji pabeigts') {
            $inputDone = (int)($validated['done_amount'] ?? 0);
            $newDone = ($task->done_amount ?? 0) + $inputDone;
            if ($newDone >= $task->production->order->daudzums) {
                $newDone = $task->production->order->daudzums;
                $validated['status'] = 'pabeigts';
            }
        } else {
            $newDone = 0;
        }

        $task->update([
            'status' => $validated['status'],
            'done_amount' => $newDone,
        ]);

        // Check if production is fully done
        $production = Production::with('tasks')->find($task->production_id);
        if ($production) {
            $highestProcessId = $production->tasks->max('process_id');
            $highestProcessTasks = $production->tasks->where('process_id', $highestProcessId);

            $allDone = $highestProcessTasks->every(fn($t) => $t->status === 'pabeigts');

            if ($allDone) {
                $order = Order::find($production->order_id);
                if ($order) {
                    $order->update(['statuss' => 'pabeigts']);
                }
                $production->delete();
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Uzdevums atjaunināts veiksmīgi.');
    }

}
