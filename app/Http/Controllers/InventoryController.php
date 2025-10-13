<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\InventoryTransfer;

class InventoryController extends Controller
{
    public function scanView()
    {
        return view('inventory.scan');
    }

    public function handleScan(Request $request)
    {
        $request->validate(['barcode' => ['required','string']]);
        $barcode = trim($request->input('barcode'));
        $product = Product::where('svitr_kods', $barcode)->first();

        if (!$product) {
            return response()->json([
                'ok' => false,
                'message' => 'Produkts ar doto svītrkodu nav atrasts.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Produkts atrasts.',
            'product' => [
                'id'         => $product->id,
                'nosaukums'  => $product->nosaukums,
                'svitr_kods' => $product->svitr_kods,
            ],
        ]);
    }

    public function storeTransfer(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'qty'        => ['required','integer','min:1'],
        ]);

        $transfer = InventoryTransfer::create([
            'product_id' => $data['product_id'],
            'qty'        => $data['qty'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Pārvietošanas ieraksts pievienots.',
            'transfer' => [
                'id' => $transfer->id,
            ],
        ]);
    }

    public function transferIndex(Request $request)
    {
        $q = $request->input('q');
        $onlyNotAccounted = (bool)$request->boolean('only_not_accounted');

        $transfers = InventoryTransfer::with(['product','creator'])
            ->when($q, function($qq) use ($q) {
                $qq->whereHas('product', function($p) use ($q) {
                    $p->where('nosaukums','like',"%$q%")
                      ->orWhere('svitr_kods','like',"%$q%");
                });
            })
            ->when($onlyNotAccounted, fn($qq) => $qq->where('accounted', false))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('inventory.transfers', compact('transfers','q','onlyNotAccounted'));
    }

    public function transferBulkAccount(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required','array'],
            'ids.*' => ['integer','exists:inventory_transfers,id'],
            'accounted' => ['nullable','boolean'],
        ]);

        $accounted = $data['accounted'] ?? true;

        InventoryTransfer::whereIn('id', $data['ids'])
            ->update([
                'accounted' => $accounted,
                'accounted_at' => $accounted ? now() : null,
            ]);

        return response()->json(['ok' => true]);
    }

    public function transferBulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids'   => ['required','array'],
            'ids.*' => ['integer','exists:inventory_transfers,id'],
        ])['ids'];

        InventoryTransfer::whereIn('id', $ids)->delete();

        return response()->json(['ok' => true, 'message' => 'Ieraksti dzēsti.']);
    }
}
