<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::ordered()->get();
        
        return view('admin.packages.index', compact('packages'));
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'max_cte_records_monthly' => 'required|integer|min:0',
            'max_documents' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'monthly_list_price' => 'nullable|numeric|min:0',
            'monthly_selling_price' => 'nullable|numeric|min:0',
            'yearly_list_price' => 'nullable|numeric|min:0',
            'yearly_selling_price' => 'nullable|numeric|min:0',
            'show_promotion' => 'boolean',
            'promotion_text' => 'nullable|string|max:255',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_visible' => 'boolean',
            'is_selectable' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $package->update($validated);

        return redirect()
            ->route('admin.packages.index')
            ->with('success', 'Package updated successfully!');
    }
}
