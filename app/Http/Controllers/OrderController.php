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
    public function index()
    {
        $orders = Order::with(['client', 'product'])->latest()->get();
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

        $order = new Order();

        $order->datums = now()->toDateString();

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

        return redirect()->route('orders.index')->with('success', 'Pasūtījums izveidots veiksmīgi!');
    }

    public function show(Order $order)
    {
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
