<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Only proceed if status was changed to 'pabeigts'
        if ($task->status !== 'pabeigts') {
            return;
        }

        // Get the production with its tasks and related order
        $production = $task->production()->with(['tasks', 'order'])->first();

        if (!$production || !$production->order) {
            return;
        }

        // Reload tasks to ensure we have up-to-date statuses
        $production->load('tasks');

        // Check if all tasks are marked as 'pabeigts'
        $allDone = $production->tasks->every(fn($t) => $t->status === 'pabeigts');

        // If all are completed, update the order status
        if ($allDone) {
            $production->order->update(['statuss' => 'pabeigts']);
        }
    }
}
