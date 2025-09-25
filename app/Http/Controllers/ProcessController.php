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
        // Table sorted by ID as requested
        $processes = Process::with('users')->orderBy('id')->get();
        return view('processes.index', compact('processes'));
    }

    public function create()
    {
        $users = User::all();
        return view('processes.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'processa_nosaukums' => 'required|string|max:255',
            'user_ids' => 'array|exists:users,id',
        ]);

        $process = Process::create([
            'processa_nosaukums' => $request->processa_nosaukums,
        ]);

        if ($request->filled('user_ids')) {
            $process->users()->attach($request->user_ids);
        }

        return redirect()->route('processes.index')->with('success', 'Process created successfully.');
    }

    public function show(Process $process)
    {
        return view('processes.show', compact('process'));
    }

    public function edit(Process $process)
    {
        $users = User::all();
        $selectedUsers = $process->users->pluck('id')->toArray();

        return view('processes.edit', compact('process', 'users', 'selectedUsers'));
    }

    public function update(Request $request, Process $process)
    {
        // Intercept ▲/▼ click (no new routes)
        if ($request->filled('swap')) {
            if ($request->swap === 'up') {
                $prev = Process::where('id', '<', $process->id)->orderBy('id', 'desc')->first();
                if ($prev) {
                    $this->swapProcessIds($prev->id, $process->id);
                    return back()->with('success', 'ID veiksmīgi samainīti (uz augšu).');
                }
                return back()->with('success', 'Šis process jau ir pašā augšā.');
            }

            if ($request->swap === 'down') {
                $next = Process::where('id', '>', $process->id)->orderBy('id', 'asc')->first();
                if ($next) {
                    $this->swapProcessIds($process->id, $next->id);
                    return back()->with('success', 'ID veiksmīgi samainīti (uz leju).');
                }
                return back()->with('success', 'Šis process jau ir pašā apakšā.');
            }
        }

        // Normal update
        $request->validate([
            'processa_nosaukums' => 'required|string|max:255',
            'user_ids' => 'array|exists:users,id',
        ]);

        $process->update([
            'processa_nosaukums' => $request->processa_nosaukums,
        ]);

        $process->users()->sync($request->user_ids ?? []);

        return redirect()->route('processes.index')->with('success', 'Process updated successfully.');
    }

    public function destroy(Process $process)
    {
        $process->delete();
        return redirect()->route('processes.index')->with('success', 'Process deleted successfully.');
    }

    /**
     * Swap two IDs in `processes` and update FK references.
     * If your FKs have ON UPDATE CASCADE, you can remove the loop over $fkRefs.
     */
    private function swapProcessIds(int $idA, int $idB): void
    {
        if ($idA === $idB) return;

        // List ALL tables/columns that reference processes.id:
        $fkRefs = [
            ['table' => 'process_user', 'column' => 'process_id'], // pivot example
            // Add more if you have them:
            // ['table' => 'orders', 'column' => 'process_id'],
            // ['table' => 'tasks',  'column' => 'process_id'],
        ];

        DB::transaction(function () use ($idA, $idB, $fkRefs) {
            $tempId = (int) DB::table('processes')->max('id') + 1;

            // Prevent FK check failures mid-swap (MySQL/MariaDB)
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // 1) Swap in parent table
            DB::table('processes')->where('id', $idA)->update(['id' => $tempId]);
            DB::table('processes')->where('id', $idB)->update(['id' => $idA]);
            DB::table('processes')->where('id', $tempId)->update(['id' => $idB]);

            // 2) Update child FK refs (skip if ON UPDATE CASCADE is present)
            foreach ($fkRefs as $ref) {
                $t = $ref['table']; $c = $ref['column'];
                DB::table($t)->where($c, $idA)->update([$c => $tempId]);
                DB::table($t)->where($c, $idB)->update([$c => $idA]);
                DB::table($t)->where($c, $tempId)->update([$c => $idB]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }
}
