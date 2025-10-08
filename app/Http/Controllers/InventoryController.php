<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class InventoryController extends Controller
{
    // Show the live barcode scan view
    public function scanView()
    {
        return view('inventory.scan');
    }

    // Handle a scanned barcode: increment daudzums_noliktava by 1 if svitr_kods matches
    public function handleScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $barcode = trim($request->input('barcode'));
        $product = Product::where('svitr_kods', $barcode)->first();

        if (!$product) {
            return response()->json([
                'ok' => false,
                'message' => 'Produkts ar doto svītrkodu nav atrasts.',
            ], 404);
        }

        // Increment inventory count
        $product->increment('daudzums_noliktava', 1);
        $product->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Daudzums noliktavā palielināts par 1.',
            'product' => [
                'id' => $product->id,
                'nosaukums' => $product->nosaukums,
                'svitr_kods' => $product->svitr_kods,
                'daudzums_noliktava' => $product->daudzums_noliktava,
            ],
        ]);
    }

    // Show a storage view listing product nosaukums + daudzums_noliktava, with search
    public function storageView(Request $request)
    {
        $q = $request->input('q');
        $query = Product::query()
            ->select(['id','nosaukums','svitr_kods','daudzums_noliktava'])
            ->orderBy('nosaukums');

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('nosaukums', 'like', '%' . $q . '%')
                    ->orWhere('svitr_kods', 'like', '%' . $q . '%');
            });
        }

        $products = $query->paginate(25)->withQueryString();

        return view('inventory.storage', compact('products','q'));
    }
    public function updateQuantity(Request $request, \App\Models\Product $product)
    {
        $data = $request->validate([
            'daudzums_noliktava' => ['required','integer','min:0'], // adjust min if negatives allowed
        ]);

        $product->update(['daudzums_noliktava' => $data['daudzums_noliktava']]);

        // Return JSON for AJAX updates
        return response()->json([
            'ok' => true,
            'product_id' => $product->id,
            'daudzums_noliktava' => $product->daudzums_noliktava,
            'message' => 'Daudzums noliktavā atjaunināts.',
        ]);
    }

}
