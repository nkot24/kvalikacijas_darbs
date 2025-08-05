<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Production;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the logged-in user's tasks.
     */
    public function index()
    {
        $user = Auth::user();

        // Show only tasks assigned to this user that are not completed
        $tasks = Task::with(['process', 'production.order'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'pabeigts')
            ->get();

        return view('tasks.index', compact('tasks'));
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
        $production = \App\Models\Production::with('tasks')->find($task->production_id);

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
            $order = \App\Models\Order::find($production->order_id);
            if ($order) {
                $order->update(['statuss' => 'pabeigts']);
            }

            // Optionally delete the production if you want (same as your ProductionController)
            $production->delete();
        }

        return redirect()->route('tasks.index')->with('success', 'Uzdevums atjaunināts veiksmīgi.');
    }




}
