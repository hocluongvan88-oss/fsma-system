<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\Product;
use Illuminate\Http\Request;

class PartnerController extends BaseController
{
    public function index(Request $request)
    {
        $query = Partner::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('partner_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('gln', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('partner_type', $request->type);
        }

        $partners = $query->latest()->paginate(20);

        return view('master-data.partners.index', compact('partners'));
    }

    public function create()
    {
        $categories = $this->getPartnerTypes();
        $products = Product::select('id', 'product_name')
            ->where('organization_id', auth()->user()->organization_id)
            ->latest()
            ->get();

        return view('master-data.partners.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWithLocale($request, [
            'partner_name' => 'required|string|max:200',
            'partner_type' => 'required|in:supplier,customer,both,processing,distribution,farm',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gln' => 'nullable|string|size:13',
        ]);

        if (auth()->check() && auth()->user()->organization_id) {
            $validated['organization_id'] = auth()->user()->organization_id;
        } else {
            return back()->withErrors(['error' => 'User must belong to an organization to create partners.']);
        }

        $partner = Partner::create($validated);

        \App\Services\QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

        return redirect()->route('master-data.partners.index')
            ->with('success', $this->getLocalizedSuccessMessage('partner_created_successfully'));
    }

    public function show(Partner $partner)
    {
        $this->authorizePartnerAccess($partner);

        $partner->load(['cteEvents' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.partners.show', compact('partner'));
    }

    public function edit(Partner $partner)
    {
        $this->authorizePartnerAccess($partner);

        $categories = $this->getPartnerTypes();
        $products = Product::select('id', 'product_name')
            ->where('organization_id', auth()->user()->organization_id)
            ->latest()
            ->get();

        return view('master-data.partners.edit', compact('partner', 'categories', 'products'));
    }

    public function update(Request $request, Partner $partner)
    {
        $this->authorizePartnerAccess($partner);

        $validated = $this->validateWithLocale($request, [
            'partner_name' => 'required|string|max:200',
            'partner_type' => 'required|in:supplier,customer,both,processing,distribution,farm',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gln' => 'nullable|string|size:13',
        ]);

        $partner->update($validated);

        \App\Services\QueryOptimizationService::clearOrganizationCache(auth()->user()->organization_id);

        return redirect()->route('master-data.partners.index')
            ->with('success', $this->getLocalizedSuccessMessage('partner_updated_successfully'));
    }

    public function destroy(Partner $partner)
    {
        $this->authorizePartnerAccess($partner);

        try {
            $partner->delete();
            return redirect()->route('master-data.partners.index')
                ->with('success', 'Partner deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete partner with existing CTE events.');
        }
    }

    private function authorizePartnerAccess(Partner $partner)
    {
        $user = auth()->user();
        
        if (!$user || ($partner->organization_id !== $user->organization_id && !$user->isAdmin())) {
            abort(403, 'Unauthorized access to this partner.');
        }
    }

    private function getPartnerTypes(): array
    {
        return [
            'supplier' => 'Nhà Cung Cấp (NCC)',
            'customer' => 'Khách Hàng (KH)',
            'processing' => 'Cơ Sở Chế Biến',
            'distribution' => 'Trung Tâm Phân Phối',
            'farm' => 'Trang Trại/Nông Trại', 
            'both' => 'Cả Hai (NCC & KH)',
        ];
    }
}
