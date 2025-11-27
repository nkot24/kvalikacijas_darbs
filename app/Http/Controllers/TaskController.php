<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Production;
use App\Models\Order;
use App\Models\TaskWorkLog;
use App\Models\ProcessProgress;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $personal = Task::with(['process', 'production.order'])
            ->where('user_id', $user->id)
            ->where('status', '!=', 'pabeigts');

        $shared = Task::with(['process', 'production.order'])
            ->whereNull('user_id')
            ->where('status', '!=', 'pabeigts')
            ->whereHas('assignedUsers', fn($q) => $q->where('users.id', $user->id));

        $tasks = $personal->union($shared)->get();

        $current = collect();
        $future  = collect();

        foreach ($tasks->groupBy('production_id') as $group) {
            $productionTasks = Task::where('production_id', $group->first()->production_id)->get();

            $unlocked = $productionTasks
                ->groupBy('process_id')
                ->sortKeys()
                ->first(fn($set) => !$set->every(fn($t) => $t->status === 'pabeigts'))
                ?->first()
                ?->process_id;

            foreach ($group as $task) {
                if ($task->process_id == $unlocked) {
                    $current->push($task);
                } else {
                    $future->push($task);
                }
            }
        }

        $priority = ['augsta' => 1, 'normāla' => 2, 'zema' => 3];

        $sort = function ($a, $b) use ($priority) {
            $pa = $priority[strtolower($a->production->order->prioritāte ?? 'zema')] ?? 3;
            $pb = $priority[strtolower($b->production->order->prioritāte ?? 'zema')] ?? 3;

            return $pa === $pb
                ? strtotime($a->production->order->izpildes_datums ?? '2100-01-01')
                    <=> strtotime($b->production->order->izpildes_datums ?? '2100-01-01')
                : $pa <=> $pb;
        };

        return view('tasks.index', [
            'currentTasks' => $current->sort($sort)->values(),
            'futureTasks'  => $future->sort($sort)->values(),
        ]);
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        return view('tasks.show', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'status'      => 'required|string|in:nav uzsākts,daļēji pabeigts,pabeigts',
            'done_amount' => 'nullable|integer|min:0',
            'spent_time'  => 'nullable|numeric|min:0.01',
            'comment'     => 'nullable|string|max:2000',
        ]);

        $orderQty = (int) data_get($task, 'production.order.daudzums', 0);
        $oldDone  = (int) $task->done_amount;
        $newDone  = $oldDone;
        $status   = $validated['status'];

        if ($status === 'pabeigts') {
            $newDone = $orderQty ?: $oldDone;
        } elseif ($status === 'daļēji pabeigts') {
            $delta = max(0, (int)$validated['done_amount']);
            if ($delta === 0) {
                return back()->withErrors(['done_amount' => 'Lūdzu norādi paveikto daudzumu.']);
            }
            $newDone = $orderQty ? min($orderQty, $oldDone + $delta) : $oldDone + $delta;
            if ($orderQty && $newDone >= $orderQty) {
                $newDone = $orderQty;
                $status  = 'pabeigts';
            }
        } else {
            $newDone = 0;
        }

        $task->update([
            'status'      => $status,
            'done_amount' => $newDone,
        ]);

        $delta = max(0, $newDone - $oldDone);
        if ($delta > 0) {
            TaskWorkLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'amount'  => $delta,
            ]);
        }

        if (in_array($status, ['daļēji pabeigts', 'pabeigts'], true)) {
            $request->validate(['spent_time' => 'required']);

            ProcessProgress::create([
                'task_id'    => $task->id,
                'process_id' => $task->process_id,
                'user_id'    => auth()->id(),
                'status'     => $status,
                'spent_time' => $validated['spent_time'],
                'comment'    => $validated['comment'],
            ]);
        }

        $production = Production::with('tasks')->find($task->production_id);

        if ($production && $production->tasks()->count() === 0) {
            Order::where('id', $production->order_id)->update(['statuss' => 'pabeigts']);
            $production->delete();
        }

        return redirect()->route('tasks.index')->with('success', 'Uzdevums atjaunināts.');
    }

    private function authorizeTask(Task $task)
    {
        $u = auth()->user();

        $isPersonal = $task->user_id === $u->id;
        $isShared   = $task->user_id === null
            && $task->process
            && $task->process->users->contains($u);

        if (!$isPersonal && !$isShared) {
            abort(403, 'Unauthorized');
        }
    }
}
