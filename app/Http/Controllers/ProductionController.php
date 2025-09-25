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
        // Validation — any file type, 100 MB cap per file
        $validated = $request->validate([
            'order_id'          => ['required', 'integer', 'exists:orders,id'],
            'process_ids'       => ['required', 'array', 'min:1'],
            'process_ids.*'     => ['integer', 'exists:processes,id'],

            'users'             => ['nullable', 'array'],
            'users.*'           => ['nullable', 'array'],
            'users.*.*'         => ['nullable', 'integer', 'exists:users,id'],

            'process_files'     => ['nullable', 'array'],
            'process_files.*'   => ['nullable', 'array'],
            'process_files.*.*' => ['nullable', 'file', 'max:102400'], // 100 MB

            // NEW: global files
            'global_files'      => ['nullable', 'array'],
            'global_files.*'    => ['nullable', 'file', 'max:102400'], // 100 MB
        ], [
            'process_ids.required'  => 'Izvēlieties vismaz vienu procesu.',
            'process_files.*.*.max' => 'Fails ir pārāk liels (maks. 100MB).',
            'global_files.*.max'    => 'Fails ir pārāk liels (maks. 100MB).',
        ]);

        DB::beginTransaction();

        try {
            // 1) Create production
            $production = Production::create([
                'order_id' => (int) $validated['order_id'],
            ]);

            // 2) Create tasks for selected processes
            foreach ($validated['process_ids'] as $processId) {
                $processId       = (int) $processId;
                $selectedUserIds = $request->input("users.$processId");

                if (is_array($selectedUserIds) && count($selectedUserIds) > 0) {
                    foreach ($selectedUserIds as $userId) {
                        Task::create([
                            'production_id' => $production->id,
                            'process_id'    => $processId,
                            'user_id'       => (int) $userId,
                            'status'        => 'nav uzsākts',
                            'done_amount'   => 0,
                        ]);
                    }
                } else {
                    Task::create([
                        'production_id' => $production->id,
                        'process_id'    => $processId,
                        'user_id'       => null,
                        'status'        => 'nav uzsākts',
                        'done_amount'   => 0,
                    ]);
                }
            }

            // 3) Save uploaded files (per-process): store once, link to all tasks of that process
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

            // 3b) NEW: Global files → attach to ALL selected processes' tasks
            if ($request->hasFile('global_files')) {
                $allTasksForSelectedProcesses = $production->tasks()
                    ->whereIn('process_id', $validated['process_ids'])
                    ->get()
                    ->groupBy('process_id');

                foreach ((array) $request->file('global_files') as $file) {
                    if (!$file) continue;

                    // Store once under production/global
                    $storedPath = $file->store(
                        "process_files/production_{$production->id}/global",
                        'public'
                    );

                    // Create a ProcessFile row for every task across all selected processes
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

            // 4) Update order status
            $order = Order::find((int) $validated['order_id']);
            if ($order) {
                $order->update(['statuss' => 'nodots ražošanai']);
            }

            DB::commit();

            return redirect()
                ->route('productions.index')
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

        // Preselect users per process (only those tasks that have a user)
        $selectedUsersByProcess = $production->tasks()
            ->select('process_id', 'user_id')
            ->whereNotNull('user_id')
            ->get()
            ->groupBy('process_id')
            ->map(fn($grp) => $grp->pluck('user_id')->unique()->values()->toArray())
            ->toArray();

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

            // NEW: global files
            'global_files'      => ['nullable', 'array'],
            'global_files.*'    => ['nullable', 'file', 'max:102400'], // 100 MB
        ], [
            'process_ids.required'  => 'Izvēlieties vismaz vienu procesu.',
            'process_files.*.*.max' => 'Fails ir pārāk liels (maks. 100MB).',
            'global_files.*.max'    => 'Fails ir pārāk liels (maks. 100MB).',
        ]);

        DB::transaction(function () use ($production, $validated, $request) {
            // 1) Update linked order
            $production->update([
                'order_id' => (int) $validated['order_id'],
            ]);

            $selectedProcessIds = collect($validated['process_ids'])
                ->map(fn($v) => (int) $v)->unique()->values();

            // Current processes in production (by tasks)
            $currentProcessIds = $production->tasks()->pluck('process_id')->unique();

            // 2) Remove tasks/files for processes that were unchecked
            $toRemove = $currentProcessIds->diff($selectedProcessIds);
            if ($toRemove->isNotEmpty()) {
                $tasksToRemove = $production->tasks()->whereIn('process_id', $toRemove)->get();
                foreach ($tasksToRemove as $task) {
                    foreach ($task->files as $file) {
                        try {
                            Storage::disk('public')->delete($file->path);
                        } catch (\Throwable $e) {
                            // ignore storage errors
                        }
                        $file->delete();
                    }
                    $task->delete();
                }
            }

            // 3) Ensure tasks for selected processes match assigned users
            foreach ($selectedProcessIds as $processId) {
                $userIds = collect(data_get($validated, "users.$processId", []))
                    ->filter()->map(fn($v) => (int) $v)->unique()->values();

                $existingTasks = $production->tasks()->where('process_id', $processId)->get();

                if ($userIds->isEmpty()) {
                    // exactly one unassigned task
                    if (!($existingTasks->count() == 1 && $existingTasks->first()->user_id === null)) {
                        foreach ($existingTasks as $t) { $t->delete(); }
                        $production->tasks()->create([
                            'process_id'  => $processId,
                            'user_id'     => null,
                            'status'      => 'nav uzsākts',
                            'done_amount' => 0,
                        ]);
                    }
                } else {
                    // one task per selected user
                    foreach ($existingTasks as $t) {
                        if ($t->user_id === null || !$userIds->contains($t->user_id)) {
                            // delete tasks that don't match desired assignment
                            foreach ($t->files as $file) {
                                try {
                                    Storage::disk('public')->delete($file->path);
                                } catch (\Throwable $e) { /* ignore */ }
                                $file->delete();
                            }
                            $t->delete();
                        }
                    }

                    $currentUserIds = $production->tasks()
                        ->where('process_id', $processId)
                        ->pluck('user_id')->filter()->values();

                    $toCreate = $userIds->diff($currentUserIds);
                    foreach ($toCreate as $uid) {
                        $production->tasks()->create([
                            'process_id'  => $processId,
                            'user_id'     => $uid,
                            'status'      => 'nav uzsākts',
                            'done_amount' => 0,
                        ]);
                    }
                }

                // 4) Optional: new files for this process → attach to all its tasks
                if ($request->hasFile("process_files.$processId")) {
                    $tasksForProcess = $production->tasks()->where('process_id', $processId)->get();

                    foreach ((array) $request->file("process_files.$processId") as $file) {
                        if (!$file) continue;

                        $storedPath = $file->store(
                            "process_files/production_{$production->id}/process_{$processId}",
                            'public'
                        );

                        foreach ($tasksForProcess as $task) {
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

            // 4b) NEW: Global files on update → attach to ALL currently selected processes' tasks
            if ($request->hasFile('global_files')) {
                $allTasksForSelectedProcesses = $production->tasks()
                    ->whereIn('process_id', $selectedProcessIds)
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
        });

        return redirect()
            ->route('productions.show', $production)
            ->with('success', 'Ražošana atjaunināta.');
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
