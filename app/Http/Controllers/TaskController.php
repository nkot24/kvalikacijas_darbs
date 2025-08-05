<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Production;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the logged-in user's tasks.
     */
    public function index()
    {
        $user = auth()->user();

        $tasks = Task::with(['process', 'production.order'])
            ->where('user_id', $user->id)
            ->get();

        $currentTasks = collect();
        $futureTasks = collect();

        // Group tasks by production
        $tasksByProduction = $tasks->groupBy('production_id');

        foreach ($tasksByProduction as $groupedTasks) {
            // Get the full production (with all tasks)
            $productionId = $groupedTasks->first()->production_id;
            $allTasksForProduction = Task::where('production_id', $productionId)->get();

            // Find the process IDs in order
            $processIds = $allTasksForProduction->pluck('process_id')->unique()->sort()->values();

            $unlockedProcessId = null;

            // Loop through process IDs and find the first one that's not completed
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
                } elseif (
                    $task->status !== 'pabeigts' &&
                    (int)$task->process_id !== (int)$unlockedProcessId
                ) {
                    // Only add unfinished future tasks that are not in the current unlocked process
                    $futureTasks->push($task);
                }
            }
        }

        return view('tasks.index', [
            'currentTasks' => $currentTasks,
            'futureTasks' => $futureTasks,
        ]);
    }

    /**
     * Show a single task.
     */
    public function show(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('tasks.show', compact('task'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        // Validate input
        $validated = $request->validate([
            'status' => 'required|string|in:nav uzsākts,daļēji pabeigts,pabeigts',
            'done_amount' => 'nullable|integer|min:0',
        ]);

        // Update the task
        $task->update([
            'status' => $validated['status'],
            'done_amount' => $validated['done_amount'] ?? $task->done_amount,
        ]);

        // Load the production with all tasks
        $production = Production::with('tasks')->find($task->production_id);

        if (!$production) {
            return redirect()->back()->with('error', 'Ražošana nav atrasta.');
        }

        // Find the highest process ID among tasks in this production
        $highestProcessId = $production->tasks->max('process_id');

        // Filter tasks of the highest process
        $highestProcessTasks = $production->tasks->where('process_id', $highestProcessId);

        // Check if all tasks for highest process are done
        $allDone = $highestProcessTasks->every(fn($t) => $t->status === 'pabeigts');

        if ($allDone) {
            // Update the order status like in ProductionController
            $order = Order::find($production->order_id);
            if ($order) {
                $order->update(['statuss' => 'pabeigts']);
            }

            // Optionally delete the production if you want (same as your ProductionController)
            $production->delete();
        }

        return redirect()->route('tasks.index')->with('success', 'Uzdevums atjaunināts veiksmīgi.');
    }
}
