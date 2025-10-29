<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    /**
     * Show organization selection page for new users
     *
     * Phương thức này đã được đổi tên từ selectOrganization() thành select()
     * để khớp với định nghĩa route 'organization.select' trong web.php.
     */
    public function select() // Đã đổi tên từ selectOrganization() thành select()
    {
        $user = auth()->user();
        
        // If user already has organization, redirect to dashboard
        if ($user->organization_id) {
            return redirect()->route('dashboard');
        }
        
        return view('organization.select');
    }
    
    /**
     * Assign user to organization
     */
    public function assignOrganization(Request $request)
    {
        $user = auth()->user();
        
        // If user already has organization, redirect to dashboard
        if ($user->organization_id) {
            return redirect()->route('dashboard')
                ->with('info', __('messages.already_in_organization'));
        }
        
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
        ]);
        
        $organization = Organization::where('name', $validated['organization_name'])
            ->where('is_active', true)
            ->first();
        
        if (!$organization) {
            return back()
                ->withInput()
                ->with('error', __('messages.organization_not_found_or_inactive'));
        }
        
        try {
            // Assign user to organization
            $user->update([
                'organization_id' => $organization->id
            ]);
            
            auth()->setUser($user->fresh());
            
            Log::info('User joined organization', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'organization_name' => $organization->name
            ]);
            
            return redirect()->route('dashboard')
                ->with('success', __('messages.successfully_joined_organization', ['name' => $organization->name]));
        } catch (\Exception $e) {
            Log::error('Failed to assign user to organization', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', __('messages.failed_to_join_organization'));
        }
    }
    
    /**
     * Create new organization for user
     */
    public function createOrganization(Request $request)
    {
        $user = auth()->user();
        
        // If user already has organization, redirect to dashboard
        if ($user->organization_id) {
            return redirect()->route('dashboard')
                ->with('info', __('messages.already_in_organization'));
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations',
            'description' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $freePackage = Package::where('slug', 'free')->first();
            $packageId = $freePackage ? $freePackage->id : 1;
            
            // Create new organization
            $organization = Organization::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
                'package_id' => $packageId,
            ]);
            
            // Assign user to new organization
            $user->update([
                'organization_id' => $organization->id
            ]);
            
            auth()->setUser($user->fresh());
            
            DB::commit();
            
            Log::info('Organization created successfully', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'organization_name' => $organization->name,
                'package_id' => $packageId,
            ]);
            
            return redirect()->route('dashboard')
                ->with('success', __('messages.organization_created_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create organization', [
                'user_id' => $user->id,
                'organization_name' => $validated['name'],
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->with('error', __('messages.failed_to_create_organization'));
        }
    }
}
