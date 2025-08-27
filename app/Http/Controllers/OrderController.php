<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exports\OrdersFullExport;
use App\Imports\OrdersFullImport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['client', 'product'])->latest()->get();
        $query = Order::with(['client', 'product']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pasutijuma_numurs', 'like', "%$search%")
                ->orWhere('klients', 'like', "%$search%")
                ->orWhereHas('client', fn($q) => $q->where('nosaukums', 'like', "%$search%"))
                ->orWhereHas('product', fn($q) => $q->where('nosaukums', 'like', "%$search%"));
            });
        }

        // Sorting
        $sortable = ['pasutijuma_numurs', 'datums', 'daudzums', 'izpildes_datums', 'prioritāte', 'statuss', 'klients'];
        $sort = $request->input('sort', 'datums');
        $direction = $request->input('direction', 'asc');

        if (!in_array($sort, $sortable)) {
            $sort = 'datums';
        }

        if ($sort === 'klients') {
            $query->leftJoin('clients', 'orders.client_id', '=', 'clients.id')
                ->orderByRaw("COALESCE(clients.nosaukums, orders.klients) $direction")
                ->select('orders.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $orders = $query->paginate(15)->appends($request->all());

        return view('orders.index', compact('orders'));
        }

    public function create()
    {
        $clients = Client::all();
        $products = Product::all();
        return view('orders.create', compact('clients', 'products'));
    }

    public function store(Request $request)
    {
        // Validate fields
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

        // Handle one-time client
        if ($request->client_id === 'vienreizējs') {
            $clientName = $request->klients;
            $clientId = null; // or create new client in DB and get ID
        } else {
            $clientId = $request->client_id;
            $clientName = null;
        }

        // Handle one-time product
        if ($request->products_id === 'vienreizējs') {
            $productName = $request->produkts;
            $productId = null; // or create new product in DB and get ID
        } else {
            $productId = $request->products_id;
            $productName = null;
        }

        // Create order
        Order::create([
            'client_id' => $clientId,
            'klients' => $clientName,
            'products_id' => $productId,
            'produkts' => $productName,
            'daudzums' => $validated['daudzums'],
            'izpildes_datums' => $validated['izpildes_datums'],
            'prioritāte' => $validated['prioritāte'],
            'piezimes' => $validated['piezimes'] ?? null,
        ]);

        return redirect()->route('orders.index')->with('success', 'Pasūtījums saglabāts veiksmīgi!');
    }


    public function show(Order $order)
    {
         $order->load([
            'client',
            'product',
            'production.tasks.process',
            'production.tasks.user',
            'production.tasks.workLogs.user',
        ]);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $clients = Client::all();
        $products = Product::all();
        return view('orders.edit', compact('order', 'clients', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'client_id'       => 'nullable|exists:clients,id',
            'klients'         => 'nullable|string|max:255',
            'products_id'     => 'nullable|exists:products,id',
            'produkts'        => 'nullable|string|max:255',
            'daudzums'        => 'required|integer|min:1',
            'izpildes_datums' => 'nullable|date',
            'prioritāte'      => 'nullable|string',
            'statuss'         => 'nullable|string',
            'piezimes'        => 'nullable|string',
        ]);

        $order->client_id = $request->client_id ?: null;
        $order->klients = $request->client_id ? null : $request->klients;

        $order->products_id = $request->products_id ?: null;
        $order->produkts = $request->products_id ? null : $request->produkts;

        $order->daudzums = $request->daudzums;
        $order->izpildes_datums = $request->izpildes_datums;
        $order->prioritāte = $request->prioritāte ?? 'normāla';
        $order->statuss = $request->statuss ?? 'nav nodots ražošanai';
        $order->piezimes = $request->piezimes;

        $order->save();

        return redirect()->route('orders.index')->with('success', 'Pasūtījums atjaunināts veiksmīgi!');
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
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new OrdersFullImport, $request->file('file'));

        return redirect()->route('orders.index')->with('success', 'Import pabeigts veiksmīgi!');
    }
    public function print(Order $order)
    {
        $order->load(['product', 'client']);
        return view('orders.print', compact('order'));
    }
}
