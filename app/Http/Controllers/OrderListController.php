<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderList;

class OrderListController extends Controller
{
    public function index()
    {
        $orderList = OrderList::all();
        return view('orderList.index', compact('orderList'));

    }
    public function create()
    {
        return view('orderList.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        OrderList::create($validated);
        return redirect()->route('orderList.index')->with('success', 'created successfully.');
    }
    
    public function destroy(OrderList $orderList)
    {
        $orderList->delete();

        return redirect()
            ->route('orderList.index')   // ← important
            ->with('success', 'deleted successfully.');
    }
}
