<?php

// app/Http/Controllers/ProcessController.php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::with('users')->get();
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

        $process->users()->attach($request->user_ids);

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
        $request->validate([
            'processa_nosaukums' => 'required|string|max:255',
            'user_ids' => 'array|exists:users,id',
        ]);

        $process->update([
            'processa_nosaukums' => $request->processa_nosaukums,
        ]);

        $process->users()->sync($request->user_ids);

        return redirect()->route('processes.index')->with('success', 'Process updated successfully.');
    }

    public function destroy(Process $process)
    {
        $process->delete();
        return redirect()->route('processes.index')->with('success', 'Process deleted successfully.');
    }
}
