<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PricingController extends Controller
{
    public function index()
    {
        $packages = Package::visible()
            ->ordered()
            ->get()
            ->map(function ($package) {
                return $package->toViewArray();
            })
            ->toArray();
        
        $currentPackage = auth()->user()->package_id ?? 'free';
        
        return view('pricing.index', compact('packages', 'currentPackage'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'package' => 'required|in:free,basic,premium,enterprise',
        ]);

        $user = auth()->user();
        $newPackageId = $request->package;
        
        $package = Package::findOrFail($newPackageId);
        
        $user->update([
            'package_id' => $package->id,
            'max_cte_records_monthly' => $package->max_cte_records_monthly,
            'max_documents' => $package->max_documents,
            'max_users' => $package->max_users,
        ]);

        return redirect()->route('dashboard')->with('success', 'Plan upgraded successfully!');
    }
}
