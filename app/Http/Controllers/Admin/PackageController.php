<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized access to package management.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $packages = Package::ordered()->get();
        
        \Log::info('[v0] Admin Package Index - Total packages found: ' . $packages->count());
        \Log::info('[v0] Package IDs: ' . $packages->pluck('id')->implode(', '));
        
        return view('admin.packages.index', compact('packages'));
    }

    public function edit($packageId)
    {
        $package = Package::where('id', $packageId)->firstOrFail();
            
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, $packageId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'max_cte_records_monthly' => 'required|integer|min:0',
            'max_documents' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:1',
            'monthly_list_price' => 'nullable|numeric|min:0',
            'monthly_selling_price' => 'nullable|numeric|min:0',
            'yearly_list_price' => 'nullable|numeric|min:0',
            'yearly_selling_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'show_promotion' => 'boolean',
            'promotion_text' => 'nullable|string|max:255',
            'features_text' => 'nullable|string',
            'is_visible' => 'boolean',
            'is_selectable' => 'boolean',
            'is_popular' => 'boolean',
            'is_highlighted' => 'boolean',
            'sort_order' => 'integer|min:0',
            'support_level' => 'nullable|string|max:50',
            'has_traceability' => 'boolean',
            'has_document_management' => 'boolean',
            'has_e_signatures' => 'boolean',
            'has_certificates' => 'boolean',
            'has_data_retention' => 'boolean',
            'has_archival' => 'boolean',
            'has_compliance_report' => 'boolean',
        ]);

        if (isset($validated['features_text'])) {
            $featuresText = $validated['features_text'];
            $validated['features'] = array_filter(
                array_map('trim', explode("\n", $featuresText)),
                fn($feature) => !empty($feature)
            );
            unset($validated['features_text']);
        }

        $validated['show_promotion'] = $request->has('show_promotion');
        $validated['is_visible'] = $request->has('is_visible');
        $validated['is_selectable'] = $request->has('is_selectable');
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_highlighted'] = $request->has('is_highlighted');
        $validated['has_traceability'] = $request->has('has_traceability');
        $validated['has_document_management'] = $request->has('has_document_management');
        $validated['has_e_signatures'] = $request->has('has_e_signatures');
        $validated['has_certificates'] = $request->has('has_certificates');
        $validated['has_data_retention'] = $request->has('has_data_retention');
        $validated['has_archival'] = $request->has('has_archival');
        $validated['has_compliance_report'] = $request->has('has_compliance_report');

        $package = Package::where('id', $packageId)->first();

        if (!$package) {
            \Log::error('[v0] Package not found for update', [
                'package_id' => $packageId,
                'user_id' => auth()->id(),
            ]);
            return redirect()
                ->route('admin.packages.index')
                ->with('error', 'Package not found!');
        }

        \Log::info('[v0] Updating package', [
            'package_id' => $packageId,
            'old_data' => $package->toArray(),
            'new_data' => $validated,
            'user_id' => auth()->id(),
        ]);

        try {
            $updated = $package->update($validated);

            if (!$updated) {
                \Log::warning('[v0] Package update returned false', [
                    'package_id' => $packageId,
                    'validated_data' => $validated,
                ]);
                return redirect()
                    ->route('admin.packages.index')
                    ->with('error', 'Failed to update package. No changes were made.');
            }

            $package->refresh();
            \Log::info('[v0] Package updated successfully', [
                'package_id' => $packageId,
                'updated_data' => $package->toArray(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.packages.index')
                ->with('success', 'Package updated successfully!');

        } catch (\Exception $e) {
            \Log::error('[v0] Error updating package', [
                'package_id' => $packageId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.packages.index')
                ->with('error', 'Error updating package: ' . $e->getMessage());
        }
    }
}
