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

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with('order', 'tasks')->get();
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
        ], [
            'process_ids.required'  => 'Izvēlieties vismaz vienu procesu.',
            'process_files.*.*.max' => 'Fails ir pārāk liels (maks. 100MB).',
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

            // 3) Save uploaded files (store once, link to all tasks of that process)
            foreach ($validated['process_ids'] as $processId) {
                $processId = (int) $processId;

                if ($request->hasFile("process_files.$processId")) {
                    $tasksForProcess = $production->tasks()
                        ->where('process_id', $processId)
                        ->get();

                    foreach ((array) $request->file("process_files.$processId") as $file) {
                        if (!$file) {
                            continue;
                        }

                        // Store once under production/process folder
                        $storedPath = $file->store(
                            "process_files/production_{$production->id}/process_{$processId}",
                            'public'
                        );

                        // Create a row for each task (scope by task_id)
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

    public function destroy(Production $production)
    {
        $production->delete();
        return redirect()->route('productions.index')->with('success', 'Ražošana dzēsta.');
    }
}
