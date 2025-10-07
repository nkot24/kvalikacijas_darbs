<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Production;
use App\Models\Order;
use App\Models\TaskWorkLog;
use App\Models\ProcessProgress; // <-- ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1) Tasks assigned directly to the user (exclude finished here)
        $personalTasks = Task::with(['process', 'production.order'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'pabeigts')   // <= add this line
            ->get();

        // 2) Shared tasks this user can work on (exclude finished here)
        $sharedTasks = Task::with(['process.users', 'production.order'])
            ->whereNull('user_id')
            ->where('status', '!=', 'pabeigts')   // <= add this line
            ->get()
            ->filter(function ($task) use ($user) {
                return $task->process && $task->process->users->contains('id', $user->id);
            });

        // Combine both task types
        $allTasks = $personalTasks->concat($sharedTasks);

        $currentTasks = collect();
        $futureTasks  = collect();

        // Group by production and decide which process is "unlocked" (first not fully done)
        $tasksByProduction = $allTasks->groupBy('production_id');

        foreach ($tasksByProduction as $groupedTasks) {
            $productionId          = $groupedTasks->first()->production_id;
            $allTasksForProduction = Task::where('production_id', $productionId)->get();

            $processIds        = $allTasksForProduction->pluck('process_id')->unique()->sort()->values();
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

        // Same variables your ZIP view expects
        return view('tasks.index', [
            'currentTasks' => $currentTasks,
            'futureTasks'  => $futureTasks,
        ]);
    }

    public function show(Task $task)
    {
        $user = auth()->user();

        // Same authorization logic as your ZIP
        if (
            ($task->user_id !== null && $task->user_id !== $user->id) ||
            ($task->user_id === null && (!$task->process || !$task->process->users->contains($user)))
        ) {
            abort(403, 'Unauthorized action.');
        }

        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $user = auth()->user();

        // Same authorization logic as your ZIP
        if (
            ($task->user_id !== null && $task->user_id !== $user->id) ||
            ($task->user_id === null && (!$task->process || !$task->process->users->contains($user)))
        ) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'status'      => 'required|string|in:nav uzsākts,daļēji pabeigts,pabeigts',
            'done_amount' => 'nullable|integer|min:0',
        ]);

        // IMPORTANT: Do NOT "claim" shared tasks anymore.
        // (Removed the block that set $task->user_id = $user->id when null.)

        // Compute new total & possible promotion to "pabeigts"
        $orderQty    = (int) data_get($task, 'production.order.daudzums', 0);
        $oldDone     = (int) ($task->done_amount ?? 0);
        $newDone     = $oldDone;
        $finalStatus = $validated['status'];

        if ($validated['status'] === 'pabeigts') {
            if ($orderQty > 0) {
                $newDone = $orderQty;
            }
        } elseif ($validated['status'] === 'daļēji pabeigts') {
            $inputDone = (int) ($validated['done_amount'] ?? 0);
            if ($inputDone <= 0) {
                return back()->withErrors(['done_amount' => 'Lūdzu norādi, cik daudz izdarīji šajā atjauninājumā.']);
            }
            $newDone = $orderQty > 0 ? min($orderQty, $oldDone + $inputDone) : ($oldDone + $inputDone);

            if ($orderQty > 0 && $newDone >= $orderQty) {
                $newDone     = $orderQty;
                $finalStatus = 'pabeigts';
            }
        } else { // 'nav uzsākts'
            $newDone = 0;
        }

        // Persist (status may have been promoted)
        $task->status      = $finalStatus;
        $task->done_amount = $newDone;
        $task->save();

        // Per-user work log (delta), but task stays shared (user_id remains null)
        $delta = max(0, $newDone - $oldDone);
        if ($delta > 0) {
            TaskWorkLog::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'amount'  => $delta,
            ]);
        }

        /* ====================== ADDED BLOCK ====================== */
        // If user marks the task as partially or fully done, require time and log progress
        if (in_array($finalStatus, ['daļēji pabeigts', 'pabeigts'], true)) {
            $request->validate([
                'spent_time' => 'required|numeric|min:0.01',
                'comment'    => 'nullable|string|max:2000',
            ], [
                'spent_time.required' => 'Lūdzu ievadiet pavadīto laiku (stundās).',
            ]);

            ProcessProgress::create([
                'task_id'    => $task->id,
                'process_id' => $task->process_id,
                'user_id'    => $user->id,
                'status'     => $finalStatus,
                'spent_time' => (float) $request->input('spent_time'), // store as hours
                'comment'    => $request->input('comment'),
            ]);
        }
        /* ==================== / END ADDED BLOCK ====================== */

        // Finish production when no tasks remain
        $production = Production::with('tasks')->find($task->production_id);
        if ($production) {
            if ($production->tasks()->count() === 0) {
                if ($order = Order::find($production->order_id)) {
                    $order->update(['statuss' => 'pabeigts']);
                }
                $production->delete();
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Uzdevums atjaunināts veiksmīgi.');
    }
}
