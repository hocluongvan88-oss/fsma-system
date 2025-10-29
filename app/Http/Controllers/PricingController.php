<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Services\CTEQuotaSyncService;
use Illuminate\Support\Facades\Log;
use App\Traits\OrganizationScope; // ✅ ĐÃ THÊM DÒNG NÀY

class PricingController extends Controller
{
    protected $quotaSyncService;

    public function __construct(CTEQuotaSyncService $quotaSyncService)
    {
        $this->quotaSyncService = $quotaSyncService;
    }

    public function index()
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;
        
        Log::info('[v0] PricingController::index - Start', [
            'user_id' => $user->id,
            'organization_id' => $organizationId,
        ]);
        
        $totalPackages = Package::withoutGlobalScope(OrganizationScope::class)->count();
        Log::info('[v0] Total packages in database', ['count' => $totalPackages]);
        
        $visiblePackages = Package::withoutGlobalScope(OrganizationScope::class)
            ->visible()
            ->count();
        Log::info('[v0] Visible packages count', ['count' => $visiblePackages]);
        
        $orgPackages = Package::withoutGlobalScope(OrganizationScope::class)
            ->visible()
            ->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                      ->orWhereNull('organization_id');
            })
            ->get();
        
        Log::info('[v0] Packages after organization filter', [
            'count' => $orgPackages->count(),
            'package_ids' => $orgPackages->pluck('id')->toArray(),
            'is_visible_values' => $orgPackages->pluck('is_visible', 'id')->toArray(),
        ]);
        
        $packages = Package::withoutGlobalScope(OrganizationScope::class)
            ->visible()
            ->where(function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId)
                      ->orWhereNull('organization_id');
            })
            ->ordered()
            ->get();
        
        if ($packages->isEmpty()) {
            Log::warning('[v0] No packages found for organization', [
                'organization_id' => $organizationId,
                'user_id' => $user->id,
                'total_packages_in_db' => $totalPackages,
                'visible_packages_in_db' => $visiblePackages,
            ]);
        }
        
        $packages = $packages->map(function ($package) {
                return $package->toViewArray();
            })
            ->toArray();
        
        Log::info('[v0] Final packages array', [
            'count' => count($packages),
            'is_empty' => empty($packages),
            'package_names' => array_column($packages, 'name'),
        ]);
        
        $currentPackage = $user->organization->package_id ?? 'free';
        
        $currentQuota = null;
        if ($user->organization) {
            try {
                $currentQuota = $this->quotaSyncService->getOrganizationQuotaStatus($user->organization->id);
            } catch (\Exception $e) {
                Log::error('Failed to get quota status: ' . $e->getMessage());
            }
        }
        
        return view('pricing.index', compact('packages', 'currentPackage', 'currentQuota'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'package' => 'required|in:free,basic,premium,enterprise',
        ]);

        $user = auth()->user();
        $organization = $user->organization;
        $newPackageId = $request->package;
        
        $package = Package::withoutGlobalScope(OrganizationScope::class)
            ->where('id', $newPackageId)
            ->where(function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id)
                      ->orWhereNull('organization_id');
            })
            ->firstOrFail();
        
        $organization->update([
            'package_id' => $package->id,
        ]);
        
        try {
            $this->quotaSyncService->syncOrganizationQuota($organization->id);
        } catch (\Exception $e) {
            Log::error('Failed to sync quota after upgrade: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')->with('success', 'Plan upgraded successfully!');
    }
}
