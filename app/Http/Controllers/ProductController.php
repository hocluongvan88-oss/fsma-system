<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SKUGenerationService;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ftl')) {
            $query->where('is_ftl', $request->ftl === 'yes');
        }

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
            'sku' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_ftl' => 'boolean',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:20',
        ]);

        if (auth()->check() && auth()->user()->organization_id) {
            $validated['organization_id'] = auth()->user()->organization_id;
        } else {
            return back()->withErrors(['error' => 'User must belong to an organization to create products.']);
        }

        if (empty($validated['sku'])) {
            $validated['sku'] = SKUGenerationService::generateUniqueSKU(auth()->user()->organization_id);
        }

        $product = Product::create($validated);

        \App\Services\QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

        return redirect()->route('master-data.products.index')
            ->with('success', $this->getLocalizedSuccessMessage('product_created_successfully'));
    }

    public function show(Product $product)
    {
        $this->authorizeProductAccess($product);

        $product->load(['traceRecords' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorizeProductAccess($product);

        return view('master-data.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProductAccess($product);

        $validated = $this->validateWithLocale($request, [
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'product_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'is_ftl' => 'boolean',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:20',
        ]);

        $product->update($validated);

        \App\Services\QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

        return redirect()->route('master-data.products.index')
            ->with('success', $this->getLocalizedSuccessMessage('product_updated_successfully'));
    }

    public function destroy(Product $product)
    {
        $this->authorizeProductAccess($product);

        try {
            $product->delete();
            return redirect()->route('master-data.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete product with existing trace records.');
        }
    }

    private function authorizeProductAccess(Product $product)
    {
        $user = auth()->user();
        
        if (!$user || ($product->organization_id !== $user->organization_id && !$user->isAdmin())) {
            abort(403, 'Unauthorized access to this product.');
        }
    }
}
