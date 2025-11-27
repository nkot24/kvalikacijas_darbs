<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Process;
use App\Models\ProcessFile;
use App\Models\Production;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::active()
            ->with(['order', 'tasks'])
            ->get();

        return view('productions.index', compact('productions'));
    }

    public function create()
    {
        return view('productions.create', [
            'orders'    => Order::where('statuss', 'nav nodots ražošanai')->get(),
            'processes' => Process::with('users')->get(),
            'users'     => User::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduction($request);

        DB::beginTransaction();

        try {
            // Create production
            $production = Production::create(['order_id' => $validated['order_id']]);

            // Create tasks
            $tasks = $this->createTasks($production, $validated['process_ids'], $request);

            // Process-specific files
            $this->handleProcessFiles($production, $tasks, $request);

            // Global files
            $this->handleGlobalFiles($production, $tasks, $request);

            // Update order status
            Order::where('id', $validated['order_id'])->update(['statuss' => 'nodots ražošanai']);

            DB::commit();

            return redirect()
                ->route('orders.show', $production->order_id)
                ->with('success', 'Ražošana izveidota veiksmīgi.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['general' => 'Neizdevās izveidot ražošanu.'])->withInput();
        }
    }


    public function edit(Production $production)
    {
        $production->load(['tasks.files', 'tasks.process', 'tasks.assignedUsers']);

        return view('productions.edit', [
            'production'             => $production,
            'orders'                 => Order::where('statuss', 'nav nodots ražošanai')
                                             ->orWhere('id', $production->order_id)
                                             ->get(),
            'processes'              => Process::with('users')->get(),
            'selectedProcessIds'     => $production->tasks->pluck('process_id')->toArray(),
            'selectedUsersByProcess' => $production->tasks
                ->mapWithKeys(fn($t) => [$t->process_id => $t->assignedUsers->pluck('id')->toArray()])
                ->toArray(),
        ]);
    }


    public function update(Request $request, Production $production)
    {
        $validated = $this->validateProduction($request);

        DB::beginTransaction();

        try {
            // Update production
            $production->update(['order_id' => $validated['order_id']]);

            // ✔ Fix: Update tasks WITHOUT deleting progress
            $tasks = $this->updateTasks($production, $validated['process_ids'], $request);

            // Process files
            $this->handleProcessFiles($production, $tasks, $request);

            // Global files
            $this->handleGlobalFiles($production, $tasks, $request);

            DB::commit();

            return redirect()
                ->route('orders.show', $production->order_id)
                ->with('success', 'Ražošana atjaunināta.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'Neizdevās atjaunināt ražošanu.'])->withInput();
        }
    }


    public function destroy(Production $production)
    {
        try {
            Storage::disk('public')->deleteDirectory("process_files/production_{$production->id}");
        } catch (\Throwable $e) {}

        $production->delete();

        return redirect()->route('productions.index')->with('success', 'Ražošana dzēsta.');
    }

    //  ───────────────────────────────────────────────────────────────
    //  HELPERS
    //  ───────────────────────────────────────────────────────────────

    private function validateProduction(Request $request)
    {
        return $request->validate([
            'order_id'      => ['required', 'exists:orders,id'],
            'process_ids'   => ['required', 'array', 'min:1'],
            'process_ids.*' => ['integer', 'exists:processes,id'],

            'users'         => ['nullable', 'array'],
            'users.*'       => ['nullable', 'array'],
            'users.*.*'     => ['nullable', 'integer', 'exists:users,id'],

            'process_files'     => ['nullable', 'array'],
            'process_files.*'   => ['nullable', 'array'],
            'process_files.*.*' => ['nullable', 'file', 'max:102400'],

            'global_files'      => ['nullable', 'array'],
            'global_files.*'    => ['nullable', 'file', 'max:102400'],
        ]);
    }

    /**
     * CREATE TASKS for production
     */
    private function createTasks(Production $production, array $processIds, Request $request)
    {
        $tasks = [];

        foreach ($processIds as $processId) {
            $task = Task::create([
                'production_id' => $production->id,
                'process_id'    => $processId,
                'status'        => 'nav uzsākts',
                'done_amount'   => 0,
            ]);

            // Assign users
            $task->assignedUsers()->sync($request->input("users.$processId", []));

            $tasks[$processId] = $task;
        }

        return $tasks;
    }


    /**
     * UPDATE TASKS — keep progress!
     */
    private function updateTasks(Production $production, array $newProcessIds, Request $request)
    {
        $existingTasks = $production->tasks->keyBy('process_id');

        $currentProcessIds = $existingTasks->keys()->toArray();

        $processesToAdd    = array_diff($newProcessIds, $currentProcessIds);
        $processesToRemove = array_diff($currentProcessIds, $newProcessIds);

        // 1️⃣ Remove tasks for processes no longer selected
        foreach ($processesToRemove as $processId) {
            $task = $existingTasks[$processId];

            foreach ($task->files as $file) {
                Storage::disk('public')->delete($file->path);
                $file->delete();
            }

            $task->delete();
        }

        $updated = [];

        // 2️⃣ Add tasks for new processes
        foreach ($processesToAdd as $processId) {
            $newTask = Task::create([
                'production_id' => $production->id,
                'process_id'    => $processId,
                'status'        => 'nav uzsākts',
                'done_amount'   => 0,
            ]);

            $newTask->assignedUsers()->sync($request->input("users.$processId", []));

            $updated[$processId] = $newTask;
        }

        // 3️⃣ Keep and update existing tasks (KEEP PROGRESS)
        foreach ($existingTasks as $processId => $task) {
            if (in_array($processId, $newProcessIds)) {
                // Update assigned users only
                $task->assignedUsers()->sync($request->input("users.$processId", []));
                $updated[$processId] = $task;
            }
        }

        return $updated;
    }




    /**
     * PROCESS-SPECIFIC FILES
     */
    private function handleProcessFiles(Production $production, array $tasks, Request $request)
    {
        foreach ($tasks as $processId => $task) {
            $files = $request->file("process_files.$processId", []);

            foreach ($files as $file) {
                $path = $file->store("process_files/production_{$production->id}/process_{$processId}", 'public');

                ProcessFile::create([
                    'process_id'    => $processId,
                    'task_id'       => $task->id,
                    'uploaded_by'   => auth()->id(),
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime'          => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }
    }


    /**
     * GLOBAL FILES → attach to all tasks
     */
    private function handleGlobalFiles(Production $production, array $tasks, Request $request)
    {
        $files = $request->file('global_files', []);

        foreach ($files as $file) {
            $storedPath = $file->store(
                "process_files/production_{$production->id}/global",
                'public'
            );

            foreach ($tasks as $processId => $task) {
                ProcessFile::create([
                    'process_id'    => $processId,
                    'task_id'       => $task->id,
                    'uploaded_by'   => auth()->id(),
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $storedPath,
                    'mime'          => $file->getClientMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }
    }
}
