<?php

namespace App\Http\Controllers;

use App\Models\OrderList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class OrderListController extends Controller
{
    public function index()
    {
        // Only active (not 'saņemts')
        $orderList = OrderList::with('creator')->active()->latest()->get();
        return view('orderList.index', compact('orderList'));
    }

    public function completed()
    {
        // Only completed ('saņemts')
        $completed = OrderList::with('creator')->completed()->latest('arrived_at')->get();
        return view('orderList.completed', compact('completed'));
    }

    public function create()
    {
        return view('orderList.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'photo'    => 'nullable|image|max:5120',
        ]);

        $path = $request->file('photo')?->store('purchases', 'public');

        OrderList::create([
            'name'       => $validated['name'],
            'quantity'   => $validated['quantity'],
            'photo_path' => $path,
            'created_by' => Auth::user()->id,
        ]);

        // Always go back to index (no redirect to completed)
        return redirect()->route('orderList.index')->with('success', 'Iepirkums izveidots veiksmīgi.');
    }

    public function edit(OrderList $orderList)
    {
        return view('orderList.edit', ['order' => $orderList]);
    }

    public function update(Request $request, OrderList $orderList)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'ordered_at'    => 'nullable|date',
            'expected_at'   => 'nullable|date|after_or_equal:ordered_at',
            'arrived_at'    => 'nullable|date',
            'photo'         => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            if ($orderList->photo_path) {
                Storage::disk('public')->delete($orderList->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('purchases', 'public');
        }

        $orderList->fill($validated)->save();

        // Always go back to index (even if status became 'saņemts')
        return redirect()
            ->route('orderList.index')
            ->with('success', $orderList->status === 'saņemts'
                ? 'Iepirkums atzīmēts kā saņemts. To redzēsi sadaļā "Izpildītie iepirkumi".'
                : 'Iepirkums atjaunināts.'
            );
    }

    public function destroy(OrderList $orderList)
    {
        $orderList->delete();
        return redirect()->route('orderList.index')->with('success', 'Dzēsts veiksmīgi.');
    }
}
