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
        
        // SỬA LỖI: Lấy tất cả sản phẩm cho mục đích chọn/liên kết (Không phân trang)
        $products = Product::select('id', 'product_name')->latest()->get(); // <-- SỬA

        return view('master-data.partners.create', compact('categories', 'products'));
    }
    // ... (các phương thức store, show không đổi)

    public function edit(Partner $partner)
    {
        $categories = $this->getPartnerTypes();
        
        // SỬA LỖI: Lấy tất cả sản phẩm cho mục đích chọn/liên kết (Không phân trang)
        // Chỉ lấy id và product_name để tối ưu bộ nhớ
        $products = Product::select('id', 'product_name')->latest()->get(); // <-- SỬA

        return view('master-data.partners.edit', compact('partner', 'categories', 'products'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $this->validateWithLocale($request, [
            'partner_name' => 'required|string|max:200',
            'partner_type' => 'required|in:supplier,customer,both,processing,distribution',
            'contact_person' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gln' => 'nullable|string|size:13',
        ]);

        $partner->update($validated);

        return redirect()->route('master-data.partners.index')
            ->with('success', $this->getLocalizedSuccessMessage('partner_updated_successfully'));
    }
    // ... (các phương thức destroy, getPartnerTypes không đổi)

    /**
     * Phương thức helper để lấy danh sách các loại đối tác (Partner Types).
     * @return array
     */
    private function getPartnerTypes(): array
    {
        // Dựa trên các giá trị ENUM trong migration partners
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
