<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Task;
use App\Models\Order;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with('order', 'tasks')->get();
        return view('productions.index', compact('productions'));
    }

    public function create()
    {
        // Only fetch orders where statuss is 'nav uzsākts'
        $orders = Order::where('statuss', 'nav uzsākts')->get();
        $processes = Process::all();
        $users = User::all();

        return view('productions.create', compact('orders', 'processes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'process_ids' => 'required|array',
            'process_ids.*' => 'exists:processes,id',
        ]);

        $production = Production::create([
            'order_id' => $validated['order_id'],
        ]);

        foreach ($validated['process_ids'] as $processId) {
            $process = \App\Models\Process::findOrFail($processId);
            foreach ($process->users as $user) {
                \App\Models\Task::create([
                    'production_id' => $production->id,
                    'process_id' => $processId,
                    'user_id' => $user->id,
                    'status' => 'nav uzsākts',
                ]);
            }
        }

        // ✅ Update order status to "nodots ražošanai"
        $order = Order::find($request->order_id);
        $order->update(['statuss' => 'nodots ražošanai']);

        return redirect()->route('productions.index')->with('success', 'Ražošana izveidota veiksmīgi.');
    }

    public function show(Production $production)
    {
        $production->load('order', 'tasks.process', 'tasks.user');
        return view('productions.show', compact('production'));
    }

    public function destroy(Production $production)
    {
        $production->delete();
        return redirect()->route('productions.index')->with('success', 'Ražošana dzēsta.');
    }
}

