<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by FTL
        if ($request->filled('ftl')) {
            $query->where('is_ftl', $request->ftl === 'yes');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->latest()->paginate(20);
        $categories = Product::distinct()->pluck('category')->filter();

        return view('master-data.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('master-data.products.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateWithLocale($request, [
            'sku' => 'required|string|max:50|unique:products',
            'product_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_ftl' => 'boolean',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:20',
        ]);

        $product = Product::create($validated);

        return redirect()->route('master-data.products.index')
            ->with('success', $this->getLocalizedSuccessMessage('product_created_successfully'));
    }

    public function show(Product $product)
    {
        $product->load(['traceRecords' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('master-data.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateWithLocale($request, [
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'product_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_ftl' => 'boolean',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:20',
        ]);

        $product->update($validated);

        return redirect()->route('master-data.products.index')
            ->with('success', $this->getLocalizedSuccessMessage('product_updated_successfully'));
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('master-data.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete product with existing trace records.');
        }
    }
}
