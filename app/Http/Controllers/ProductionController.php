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
        $orders    = Order::where('statuss', 'nav nodots ražošanai')->get();
        $processes = Process::with('users')->get();
        $users     = User::all();

        return view('productions.create', compact('orders', 'processes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'          => ['required', 'integer', 'exists:orders,id'],
            'process_ids'       => ['required', 'array', 'min:1'],
            'process_ids.*'     => ['integer', 'exists:processes,id'],

            'users'             => ['nullable', 'array'],
            'users.*'           => ['nullable', 'array'],
            'users.*.*'         => ['nullable', 'integer', 'exists:users,id'],

            'process_files'     => ['nullable', 'array'],
            'process_files.*'   => ['nullable', 'array'],
            'process_files.*.*' => ['nullable', 'file', 'max:102400'], // 100MB

            'global_files'      => ['nullable', 'array'],
            'global_files.*'    => ['nullable', 'file', 'max:102400'], // 100MB
        ], [
            'process_ids.required'  => 'Izvēlieties vismaz vienu procesu.',
            'process_files.*.*.max' => 'Fails ir pārāk liels (maks. 100MB).',
            'global_files.*.max'    => 'Fails ir pārāk liels (maks. 100MB).',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create production
            $production = Production::create([
                'order_id' => (int) $validated['order_id'],
            ]);

            // 2️⃣ Create one shared task per selected process
            foreach ($validated['process_ids'] as $processId) {
                $task = Task::create([
                    'production_id' => $production->id,
                    'process_id'    => (int) $processId,
                    'user_id'       => null, // shared
                    'status'        => 'nav uzsākts',
                    'done_amount'   => 0,
                ]);

                // Attach selected users to the shared task
                $selectedUserIds = $request->input("users.$processId");
                if (is_array($selectedUserIds) && count($selectedUserIds) > 0) {
                    $task->assignedUsers()->sync($selectedUserIds);
                }
            }

            // 3️⃣ Save uploaded files (per-process)
            foreach ($validated['process_ids'] as $processId) {
                $processId = (int) $processId;

                if ($request->hasFile("process_files.$processId")) {
                    $tasksForProcess = $production->tasks()
                        ->where('process_id', $processId)
                        ->get();

                    foreach ((array) $request->file("process_files.$processId") as $file) {
                        if (!$file) continue;

                        $storedPath = $file->store(
                            "process_files/production_{$production->id}/process_{$processId}",
                            'public'
                        );

                        foreach ($tasksForProcess as $task) {
                            ProcessFile::create([
                                'process_id'    => $processId,
                                'task_id'       => (int) $task->id,
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

            // 4️⃣ Global files → attach to all tasks
            if ($request->hasFile('global_files')) {
                $allTasksForSelectedProcesses = $production->tasks()
                    ->whereIn('process_id', $validated['process_ids'])
                    ->get()
                    ->groupBy('process_id');

                foreach ((array) $request->file('global_files') as $file) {
                    if (!$file) continue;

                    $storedPath = $file->store(
                        "process_files/production_{$production->id}/global",
                        'public'
                    );

                    foreach ($allTasksForSelectedProcesses as $processId => $tasks) {
                        foreach ($tasks as $task) {
                            ProcessFile::create([
                                'process_id'    => (int) $processId,
                                'task_id'       => (int) $task->id,
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

            // 5️⃣ Update order status
            $order = Order::find((int) $validated['order_id']);
            if ($order) {
                $order->update(['statuss' => 'nodots ražošanai']);
            }

            DB::commit();

            return redirect()
                ->route('orders.show', $production->order_id)
                ->with('success', 'Ražošana izveidota veiksmīgi.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['general' => 'Neizdevās izveidot ražošanu.'])
                ->withInput();
        }
    }


    public function show(Production $production)
    {
        $production->load([
            'order',
            'tasks.process',
            'tasks.user',
            'tasks.workLogs.user',
            'tasks.files',
        ]);

        $allTasks = $production->tasks->sortBy('process_id');

        return view('productions.show', compact('production', 'allTasks'));
    }

    /**
     * EDIT production
     */
   public function edit(Production $production)
    {
        $orders = Order::where('statuss', 'nav nodots ražošanai')
            ->orWhere('id', $production->order_id) // allow current order to remain selectable
            ->get();

        $processes = Process::with('users')->get();

        // Eager-load tasks + files (+ process) so the view can show existing files
        $production->load([
            'order',
            'tasks.files',
            'tasks.process',
            // 'tasks.user', // only if you need it in the edit view
        ]);

        // Preselect processes used in this production
        $selectedProcessIds = $production->tasks()
            ->pluck('process_id')->unique()->toArray();

        $selectedUsersByProcess = [];

        foreach ($production->tasks as $task) {
            $processId = $task->process_id;
            $userIds = $task->assignedUsers()->pluck('users.id')->toArray();

            if (!isset($selectedUsersByProcess[$processId])) {
                $selectedUsersByProcess[$processId] = [];
            }

            $selectedUsersByProcess[$processId] = array_unique(
                array_merge($selectedUsersByProcess[$processId], $userIds)
            );
        }

        return view('productions.edit', [
            'production'             => $production,
            'orders'                 => $orders,
            'processes'              => $processes,
            'selectedProcessIds'     => $selectedProcessIds,
            'selectedUsersByProcess' => $selectedUsersByProcess,
        ]);
    }

    /**
     * UPDATE production
     */
    public function update(Request $request, Production $production)
    {
        $validated = $request->validate([
            'order_id'          => ['required', 'integer', 'exists:orders,id'],
            'process_ids'       => ['required', 'array', 'min:1'],
            'process_ids.*'     => ['integer', 'exists:processes,id'],

            'users'             => ['nullable', 'array'],
            'users.*'           => ['nullable', 'array'],
            'users.*.*'         => ['nullable', 'integer', 'exists:users,id'],

            'process_files'     => ['nullable', 'array'],
            'process_files.*'   => ['nullable', 'array'],
            'process_files.*.*' => ['nullable', 'file', 'max:102400'], // 100MB

            'global_files'      => ['nullable', 'array'],
            'global_files.*'    => ['nullable', 'file', 'max:102400'], // 100 MB
        ], [
            'process_ids.required'  => 'Izvēlieties vismaz vienu procesu.',
            'process_files.*.*.max' => 'Fails ir pārāk liels (maks. 100MB).',
            'global_files.*.max'    => 'Fails ir pārāk liels (maks. 100MB).',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Update production
            $production->update([
                'order_id' => (int) $validated['order_id'],
            ]);

            // 2️⃣ Remove old tasks/files for processes that were unchecked
            $currentProcessIds = $production->tasks()->pluck('process_id')->unique();
            $selectedProcessIds = collect($validated['process_ids'])->map(fn($id) => (int)$id);

            $toRemove = $currentProcessIds->diff($selectedProcessIds);
            if ($toRemove->isNotEmpty()) {
                $tasksToRemove = $production->tasks()->whereIn('process_id', $toRemove)->get();
                foreach ($tasksToRemove as $task) {
                    foreach ($task->files as $file) {
                        try {
                            Storage::disk('public')->delete($file->path);
                        } catch (\Throwable $e) { /* ignore */ }
                        $file->delete();
                    }
                    $task->delete();
                }
            }

            // 3️⃣ Create shared task per selected process
            foreach ($selectedProcessIds as $processId) {
                // Delete existing tasks and their files for this process
                $existingTasks = $production->tasks()->where('process_id', $processId)->get();
                foreach ($existingTasks as $task) {
                    foreach ($task->files as $file) {
                        try {
                            Storage::disk('public')->delete($file->path);
                        } catch (\Throwable $e) {}
                        $file->delete();
                    }
                    $task->delete();
                }

                // Create new shared task
                $task = Task::create([
                    'production_id' => $production->id,
                    'process_id'    => $processId,
                    'user_id'       => null,
                    'status'        => 'nav uzsākts',
                    'done_amount'   => 0,
                ]);

                // Attach users (if any)
                $selectedUserIds = $request->input("users.$processId");
                if (is_array($selectedUserIds) && count($selectedUserIds) > 0) {
                    $task->assignedUsers()->sync($selectedUserIds);
                }

                // 4️⃣ Process-specific files
                if ($request->hasFile("process_files.$processId")) {
                    foreach ((array) $request->file("process_files.$processId") as $file) {
                        if (!$file) continue;

                        $storedPath = $file->store(
                            "process_files/production_{$production->id}/process_{$processId}",
                            'public'
                        );

                        ProcessFile::create([
                            'process_id'    => $processId,
                            'task_id'       => $task->id,
                            'uploaded_by'   => optional($request->user())->id,
                            'original_name' => $file->getClientOriginalName(),
                            'path'          => $storedPath,
                            'mime'          => $file->getClientMimeType(),
                            'size'          => $file->getSize(),
                        ]);
                    }
                }
            }

            // 5️⃣ Global files → attach to all current tasks
            if ($request->hasFile('global_files')) {
                $allTasks = $production->tasks()->get()->groupBy('process_id');

                foreach ((array) $request->file('global_files') as $file) {
                    if (!$file) continue;

                    $storedPath = $file->store(
                        "process_files/production_{$production->id}/global",
                        'public'
                    );

                    foreach ($allTasks as $processId => $tasks) {
                        foreach ($tasks as $task) {
                            ProcessFile::create([
                                'process_id'    => (int) $processId,
                                'task_id'       => (int) $task->id,
                                'uploaded_by'   => optional($request->user())->id,
                                'original_name' => $file->getClientOriginalName(),
                                'path'          => $storedPath,
                                'mime'          => $file->getClientMimeType(),
                                'size'          => $file->getSize(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('orders.show', $production->order_id)
                ->with('success', 'Ražošana atjaunināta.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withErrors(['general' => 'Neizdevās atjaunināt ražošanu.'])
                ->withInput();
        }
    }

    public function destroy(Production $production)
    {
        // Optional: delete stored files for this production
        try {
            Storage::disk('public')->deleteDirectory("process_files/production_{$production->id}");
        } catch (\Throwable $e) {
            // ignore storage errors
        }

        $production->delete();
        return redirect()->route('productions.index')->with('success', 'Ražošana dzēsta.');
    }
}
