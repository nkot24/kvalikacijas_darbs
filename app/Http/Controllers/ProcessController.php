<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::with('users')
            ->orderBy('id')
            ->get();

        return view('processes.index', compact('processes'));
    }

    public function create()
    {
        $users = User::all();

        return view('processes.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'processa_nosaukums' => ['required', 'string', 'max:255'],
            'user_ids'           => ['nullable', 'array'],
            'user_ids.*'         => ['integer', 'exists:users,id'],
        ]);

        $process = Process::create([
            'processa_nosaukums' => $data['processa_nosaukums'],
        ]);

        $process->users()->attach($data['user_ids'] ?? []);

        return redirect()
            ->route('processes.index')
            ->with('success', 'Process created successfully.');
    }

    public function show(Process $process)
    {
        $process->load('users', 'progress', 'files');

        return view('processes.show', compact('process'));
    }

    public function edit(Process $process)
    {
        $users         = User::all();
        $selectedUsers = $process->users->pluck('id')->toArray();

        return view('processes.edit', compact('process', 'users', 'selectedUsers'));
    }

    public function update(Request $request, Process $process)
    {
        // Handle ▲ / ▼ swap
        if ($request->filled('swap')) {
            $other = $request->swap === 'up'
                ? Process::where('id', '<', $process->id)->orderByDesc('id')->first()
                : Process::where('id', '>', $process->id)->orderBy('id')->first();

            if (! $other) {
                return back()->with('success', 'Vairs nav ko samainīt.');
            }

            $this->swapProcessIds($process->id, $other->id);

            return back()->with('success', 'Procesu ID veiksmīgi samainīti.');
        }

        // Normal update
        $data = $request->validate([
            'processa_nosaukums' => ['required', 'string', 'max:255'],
            'user_ids'           => ['nullable', 'array'],
            'user_ids.*'         => ['integer', 'exists:users,id'],
        ]);

        $process->update([
            'processa_nosaukums' => $data['processa_nosaukums'],
        ]);

        $process->users()->sync($data['user_ids'] ?? []);

        return redirect()
            ->route('processes.index')
            ->with('success', 'Process updated successfully.');
    }

    public function destroy(Process $process)
    {
        $process->delete();

        return redirect()
            ->route('processes.index')
            ->with('success', 'Process deleted successfully.');
    }

    /**
     * Swap two process IDs.
     * Requires all foreign keys on process_id to have ON UPDATE CASCADE.
     */
    private function swapProcessIds(int $idA, int $idB): void
    {
        if ($idA === $idB) {
            return;
        }

        DB::transaction(function () use ($idA, $idB) {
            // use a temp ID that doesn't exist
            $tempId = Process::max('id') + 1;

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // 1) Swap in processes table
            DB::table('processes')->where('id', $idA)->update(['id' => $tempId]);
            DB::table('processes')->where('id', $idB)->update(['id' => $idA]);
            DB::table('processes')->where('id', $tempId)->update(['id' => $idB]);

            // 2) Swap in all child tables that have process_id
            $tables = [
                'process_user',
                'tasks',
                'process_progresses',
                'process_files',
            ];

            foreach ($tables as $table) {
                DB::table($table)->where('process_id', $idA)->update(['process_id' => $tempId]);
                DB::table($table)->where('process_id', $idB)->update(['process_id' => $idA]);
                DB::table($table)->where('process_id', $tempId)->update(['process_id' => $idB]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }
}
