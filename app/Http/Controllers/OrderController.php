<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exports\OrdersFullExport;
use App\Imports\OrdersFullImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client', 'product'])->where('statuss', '!=', 'pabeigts');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pasutijuma_numurs', 'like', "%$search%")
                    ->orWhere('klients', 'like', "%$search%")
                    ->orWhereHas('client', fn($q) => $q->where('nosaukums', 'like', "%$search%"))
                    ->orWhereHas('product', fn($q) => $q->where('nosaukums', 'like', "%$search%"));
            });
        }

        $sortable = ['pasutijuma_numurs', 'datums', 'daudzums', 'izpildes_datums', 'prioritāte', 'statuss', 'klients'];
        $sort = in_array($request->input('sort'), $sortable) ? $request->input('sort') : 'datums';
        $direction = $request->input('direction', 'asc');

        if ($sort === 'klients') {
            $query->leftJoin('clients', 'orders.client_id', '=', 'clients.id')
                  ->orderByRaw("COALESCE(clients.nosaukums, orders.klients) $direction")
                  ->select('orders.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $orders = $query->paginate(50)->appends($request->all());

        return view('orders.index', compact('orders'));
    }

    public function complete(Request $request)
    {
        $query = Order::with(['client', 'product'])->where('statuss', 'pabeigts');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pasutijuma_numurs', 'like', "%$search%")
                    ->orWhere('klients', 'like', "%$search%")
                    ->orWhereHas('client', fn($q) => $q->where('nosaukums', 'like', "%$search%"))
                    ->orWhereHas('product', fn($q) => $q->where('nosaukums', 'like', "%$search%"));
            });
        }

        $query->orderBy($request->input('sort', 'datums'), $request->input('direction', 'desc'));
        $orders = $query->paginate(15)->appends($request->all());

        return view('orders.complete', compact('orders'));
    }

    public function create()
    {
        return view('orders.create', [
            'clients' => Client::all(),
            'products' => Product::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|string',
            'klients' => 'nullable|string|max:255',
            'products_id' => 'nullable|string',
            'produkts' => 'nullable|string|max:255',
            'daudzums' => 'required|integer|min:1',
            'izpildes_datums' => 'required|date',
            'prioritāte' => 'required|in:zema,normāla,augsta',
            'piezimes' => 'nullable|string',
        ]);

        $order = Order::create([
            'client_id' => $request->client_id === 'vienreizējs' ? null : $request->client_id,
            'klients' => $request->client_id === 'vienreizējs' ? $request->klients : null,
            'products_id' => $request->products_id === 'vienreizējs' ? null : $request->products_id,
            'produkts' => $request->products_id === 'vienreizējs' ? $request->produkts : null,
            'daudzums' => $validated['daudzums'],
            'izpildes_datums' => $validated['izpildes_datums'],
            'prioritāte' => $validated['prioritāte'],
            'piezimes' => $validated['piezimes'] ?? null,
        ]);

        return redirect()->route('orders.show', $order->id)
                 ->with('success', 'Pasūtījums saglabāts veiksmīgi!');
    }

    public function show(Order $order)
    {
        $order->load([
            'client', 'product',
            'production.tasks.process',
            'production.tasks.user',
            'production.tasks.workLogs.user',
        ]);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('orders.edit', [
            'order' => $order,
            'clients' => Client::all(),
            'products' => Product::all()
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'klients' => 'nullable|string|max:255',
            'products_id' => 'nullable|exists:products,id',
            'produkts' => 'nullable|string|max:255',
            'daudzums' => 'required|integer|min:1',
            'izpildes_datums' => 'nullable|date',
            'prioritāte' => 'nullable|string',
            'statuss' => 'nullable|string',
            'piezimes' => 'nullable|string',
        ]);

        $order->update([
            'client_id' => $request->client_id ?: null,
            'klients' => $request->client_id ? null : $request->klients,
            'products_id' => $request->products_id ?: null,
            'produkts' => $request->products_id ? null : $request->produkts,
            'daudzums' => $validated['daudzums'],
            'izpildes_datums' => $request->izpildes_datums,
            'prioritāte' => $request->prioritāte ?? 'normāla',
            'statuss' => $request->statuss ?? 'nav nodots ražošanai',
            'piezimes' => $request->piezimes,
        ]);

        // If completed, remove all related production data
        if ($order->statuss === 'pabeigts' && $order->production) {
            foreach ($order->production->tasks as $task) {
                foreach ($task->files as $file) {
                    Storage::disk('public')->delete($file->path);
                    $file->delete();
                }
                $task->assignedUsers()?->detach();
                $task->delete();
            }

            Storage::disk('public')->deleteDirectory("process_files/production_{$order->production->id}");
            $order->production->delete();
        }

        return redirect()->route('orders.show', $order)->with('success', 'Pasūtījums atjaunināts veiksmīgi!');
    }

    public function destroy(Order $order)
    {
        $order->forceDelete();
        return redirect()->route('orders.index')->with('success', 'Pasūtījums dzēsts veiksmīgi!');
    }

    public function fullExport()
    {
        return Excel::download(new OrdersFullExport, 'orders_full.xlsx');
    }

    public function fullImport(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        Excel::import(new OrdersFullImport, $request->file('file'));
        return redirect()->route('orders.index')->with('success', 'Import pabeigts veiksmīgi!');
    }

    public function print(Order $order)
    {
        return view('orders.print', ['order' => $order->load(['product', 'client'])]);
    }
}
