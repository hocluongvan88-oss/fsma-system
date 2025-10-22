<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Pricing;
use Illuminate\Http\Request;

class PricingController extends BaseController
{
    public function index()
    {
        $pricings = Pricing::all();
        return view('admin.pricing.index', compact('pricings'));
    }

    public function edit(Pricing $pricing)
    {
        return view('admin.pricing.edit', compact('pricing'));
    }

    public function update(Request $request, Pricing $pricing)
    {
        $validated = $this->validateWithLocale($request, [
            'package_name' => 'required|string|max:255',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'list_price_monthly' => 'required|numeric|min:0',
            'list_price_yearly' => 'required|numeric|min:0',
            'max_cte_records_monthly' => 'required|integer|min:0',
            'max_documents' => 'required|integer|min:0',
            'max_users' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $pricing->update($validated);

        return redirect()->route('admin.pricing.index')
            ->with('success', $this->getLocalizedSuccessMessage('pricing_updated_successfully'));
    }
}
