<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    private const SORTABLE = ['pasutijuma_numurs', 'datums', 'daudzums', 'izpildes_datums', 'prioritāte', 'statuss', 'klients'];

    public function index(Request $request)
    {
        $query = Order::with(['client', 'product'])->active();

        $this->applySearch($query, $request->input('search'));

        $sort = in_array($request->input('sort'), self::SORTABLE) ? $request->input('sort') : 'datums';
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
        $query = Order::with(['client', 'product'])->completed();

        $this->applySearch($query, $request->input('search'));

        $sort = in_array($request->input('sort'), self::SORTABLE) ? $request->input('sort') : 'datums';
        $query->orderBy($sort, $request->input('direction', 'desc'));

        $orders = $query->paginate(15)->appends($request->all());

        return view('orders.complete', compact('orders'));
    }

    public function create()
    {
        return view('orders.create', [
            'clients' => Client::all(),
            'products' => Product::all(),
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

        [$clientId, $klients] = $this->resolveClientField($request->client_id, $request->klients);
        [$productsId, $produkts] = $this->resolveProductField($request->products_id, $request->produkts);

        $order = Order::create([
            'client_id' => $clientId,
            'klients' => $klients,
            'products_id' => $productsId,
            'produkts' => $produkts,
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
            'products' => Product::all(),
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

        [$clientId, $klients] = $this->resolveClientField($request->client_id, $request->klients);
        [$productsId, $produkts] = $this->resolveProductField($request->products_id, $request->produkts);

        $order->update([
            'client_id' => $clientId,
            'klients' => $klients,
            'products_id' => $productsId,
            'produkts' => $produkts,
            'daudzums' => $validated['daudzums'],
            'izpildes_datums' => $request->izpildes_datums,
            'prioritāte' => $request->prioritāte ?? 'normāla',
            'statuss' => $request->statuss ?? 'nav nodots ražošanai',
            'piezimes' => $request->piezimes,
        ]);

        if ($order->statuss === 'pabeigts') {
            $order->load('production.tasks.files');
            $order->deleteProductionData();
        }

        return redirect()->route('orders.show', $order)->with('success', 'Pasūtījums atjaunināts veiksmīgi!');
    }

    public function destroy(Order $order)
    {
        $order->forceDelete();

        return redirect()->route('orders.index')->with('success', 'Pasūtījums dzēsts veiksmīgi!');
    }

    

    public function print(Order $order)
    {
        return view('orders.print', ['order' => $order->load(['product', 'client'])]);
    }

    // Private helpers

    private function applySearch($query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('pasutijuma_numurs', 'like', "%$search%")
              ->orWhere('klients', 'like', "%$search%")
              ->orWhereHas('client', fn($q) => $q->where('nosaukums', 'like', "%$search%"))
              ->orWhereHas('product', fn($q) => $q->where('nosaukums', 'like', "%$search%"));
        });
    }

    private function resolveClientField(?string $clientId, ?string $klients): array
    {
        if ($clientId === 'vienreizējs' || ! $clientId) {
            return [null, $klients];
        }

        return [$clientId, null];
    }

    private function resolveProductField(?string $productsId, ?string $produkts): array
    {
        if ($productsId === 'vienreizējs' || ! $productsId) {
            return [null, $produkts];
        }

        return [$productsId, null];
    }
}
