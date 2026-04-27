<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        $sort_by = $request->get('sort_by', 'svitr_kods'); // Default sorting column
        $sort_order = $request->get('sort_order', 'asc');  // Default sorting order

        // Allow only specific sortable columns
        $allowedSorts = ['svitr_kods', 'nosaukums'];

        if (!in_array($sort_by, $allowedSorts)) {
            $sort_by = 'svitr_kods';
        }

        $products = Product::orderBy($sort_by, $sort_order)->get();

        return view('products.index', compact('products', 'sort_by', 'sort_order'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'svitr_kods' => 'required|numeric|unique:products',
            'nosaukums' => 'required|string|max:255',
            'pardosanas_cena' => 'required|numeric',
            'vairumtirdzniecibas_cena' => 'nullable|numeric',
            'daudzums_noliktava' => 'nullable|integer',
            'svars_neto' => 'nullable|numeric',
            'nomGr_kods' => 'required|string|max:255',
            'garums' => 'nullable|numeric',
            'platums' => 'nullable|numeric',
            'augstums' => 'nullable|numeric',
        ]);

        Product::create($validated);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'svitr_kods' => 'required|numeric|unique:products,svitr_kods,' . $product->id,
            'nosaukums' => 'required|string|max:255',
            'pardosanas_cena' => 'required|numeric',
            'vairumtirdzniecibas_cena' => 'nullable|numeric',
            'daudzums_noliktava' => 'nullable|integer',
            'svars_neto' => 'nullable|numeric',
            'nomGr_kods' => 'required|string|max:255',
            'garums' => 'nullable|numeric',
            'platums' => 'nullable|numeric',
            'augstums' => 'nullable|numeric',
        ]);

        $product->update($validated);
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    
}
