<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Package;
use App\Services\CTEQuotaSyncService;
use Illuminate\Support\Facades\DB;

class UserController extends BaseController
{
    protected $quotaSyncService;

    public function __construct(CTEQuotaSyncService $quotaSyncService)
    {
        $this->quotaSyncService = $quotaSyncService;
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);
        
        $currentUser = auth()->user();
        $query = User::orderBy('created_at', 'desc');
        
        if ($currentUser->isSystemAdmin()) {
            // System admin sees all users
        } elseif ($currentUser->isAdmin()) {
            // Organization admin sees all users
        } elseif ($currentUser->organization_id) {
            // Non-admin with organization sees only users in their organization
            $query->where('organization_id', $currentUser->organization_id);
        } else {
            // Non-admin without organization sees only themselves
            $query->where('id', $currentUser->id);
        }
        
        if ($currentUser->isManager() && !$currentUser->isAdmin()) {
            $query->whereIn('role', ['manager', 'operator']);
        }
        
        if (!$currentUser->isAdmin()) {
            $query->where('email', '!=', 'admin@fsma204.com');
        }
        
        $users = $query->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $organizations = Organization::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $packages = Package::visible()->ordered()->get();
        
        return view('users.create', compact('organizations', 'packages'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        // First, validate the input
        $allowedRoles = $currentUser->isAdmin() 
            ? ['admin', 'manager', 'operator'] 
            : ['manager', 'operator'];

        $validationRules = [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'full_name' => 'required|string|max:255',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'role' => ['required', Rule::in($allowedRoles)],
            'is_active' => 'boolean',
            'is_system_admin' => 'boolean',
            'organization_scenario' => 'required|in:existing,new',
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_name' => 'nullable|string|max:255',
        ];

        if ($request->input('organization_scenario') === 'existing') {
            $validationRules['organization_id'] = 'required|exists:organizations,id';
        } elseif ($request->input('organization_scenario') === 'new') {
            $validationRules['organization_name'] = 'required|string|max:255|unique:organizations,name';
            if ($currentUser->isAdmin()) {
                $validationRules['package_id'] = 'required|exists:packages,id';
            }
        }

        if ($currentUser->isAdmin() && $request->input('organization_scenario') !== 'new') {
            $validationRules['package_id'] = 'required|exists:packages,id';
        }

        $validated = $this->validateWithLocale($request, $validationRules);

        if (!$currentUser->isAdmin()) {
            return back()->withInput()
                ->with('error', $this->getLocalizedErrorMessage('no_permission_create_admin'));
        }

        if ($request->has('is_system_admin') && $request->boolean('is_system_admin')) {
            $validated['is_system_admin'] = true;
            $validated['organization_id'] = null;
        } else {
            $validated['is_system_admin'] = false;
            
            if ($validated['organization_scenario'] === 'new') {
                $packageId = $validated['package_id'] ?? null;
                if (!$packageId) {
                    $freePackage = Package::where('slug', 'free')->first();
                    $packageId = $freePackage ? $freePackage->id : 1;
                }

                $newOrganization = Organization::create([
                    'name' => $validated['organization_name'],
                    'is_active' => true,
                    'package_id' => $packageId,
                ]);

                $validated['organization_id'] = $newOrganization->id;
                $organization = $newOrganization;
            } else {
                // Existing organization scenario
                $organization = Organization::find($validated['organization_id']);
                if (!$organization) {
                    return back()->withInput()
                        ->with('error', 'Selected organization not found.');
                }
            }
        }

        try {
            $user = DB::transaction(function() use ($validated, $organization, $currentUser) {
                if ($organization) {
                    // Lock the organization and quota records to prevent race conditions
                    $lockedOrganization = Organization::lockForUpdate()->find($organization->id);
                    
                    if (!$lockedOrganization) {
                        throw new \Exception('Organization not found or locked.');
                    }

                    // Lock the quota record as well
                    $quota = $lockedOrganization->quotas()
                        ->where('feature_name', 'users')
                        ->lockForUpdate()
                        ->first();

                    // Check quota for non-admin users
                    if (!$currentUser->isAdmin()) {
                        $activeUserCount = User::where('organization_id', $lockedOrganization->id)
                            ->where('is_active', true)
                            ->count();
                        
                        $package = $lockedOrganization->getPackage();
                        $userLimit = $package ? $package->max_users : 0;
                        
                        if ($activeUserCount >= $userLimit && $userLimit > 0) {
                            throw new \Exception("You have reached your user limit ({$userLimit} users). Please upgrade your package to add more users.");
                        }
                    }

                    // Increment quota within the same transaction
                    $this->quotaSyncService->incrementUserUsage($lockedOrganization);
                }

                $validated['password'] = Hash::make($validated['password']);
                $validated['is_active'] = request()->has('is_active');
                
                unset($validated['organization_scenario']);
                unset($validated['organization_name']);
                unset($validated['package_id']);
                
                return User::create($validated);
            });

            return redirect()->route('admin.users.index')
                ->with('success', $this->getLocalizedSuccessMessage('user_created_successfully'));
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isSystemAdmin() && !$currentUser->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized access to administrator.');
            }
            
            if ($user->organization_id !== $currentUser->organization_id) {
                abort(403, 'Unauthorized access to this user.');
            }
            
            if ($user->organization_id === null || $currentUser->organization_id === null) {
                abort(403, 'Invalid organization access.');
            }
        }
        
        $organizations = Organization::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $packages = Package::visible()->ordered()->get();
        
        return view('users.edit', compact('user', 'organizations', 'packages'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isSystemAdmin() && !$currentUser->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized access to administrator.');
            }
            
            if ($user->organization_id !== $currentUser->organization_id) {
                abort(403, 'Unauthorized access to this user.');
            }
            
            if ($user->organization_id === null || $currentUser->organization_id === null) {
                abort(403, 'Invalid organization access.');
            }
            
            if ($user->role === 'admin') {
                return back()->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
            }
        }

        $allowedRoles = $currentUser->isAdmin() 
            ? ['admin', 'manager', 'operator'] 
            : ['manager', 'operator'];

        $validationRules = [
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'required|string|max:255',
            'role' => ['required', Rule::in($allowedRoles)],
            'is_active' => 'boolean',
            'is_system_admin' => 'boolean',
        ];

        // Only admin can change organization assignment
        if ($currentUser->isAdmin()) {
            $validationRules['organization_scenario'] = 'required|in:existing,new';
            $validationRules['organization_id'] = 'nullable|exists:organizations,id';
            $validationRules['organization_name'] = 'nullable|string|max:255';

            if ($request->input('organization_scenario') === 'existing') {
                $validationRules['organization_id'] = 'required|exists:organizations,id';
            } elseif ($request->input('organization_scenario') === 'new') {
                $validationRules['organization_name'] = 'required|string|max:255|unique:organizations,name';
                $validationRules['package_id'] = 'required|exists:packages,id';
            }
        }

        $validated = $this->validateWithLocale($request, $validationRules);

        if (!$currentUser->isAdmin() && $validated['role'] === 'admin') {
            return back()->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
        }

        if ($request->has('is_system_admin') && $request->boolean('is_system_admin')) {
            $validated['is_system_admin'] = true;
            $validated['organization_id'] = null;
        } else {
            $validated['is_system_admin'] = false;
            
            if ($currentUser->isAdmin() && isset($validated['organization_scenario'])) {
                if ($validated['organization_scenario'] === 'new') {
                    $packageId = $validated['package_id'] ?? null;
                    if (!$packageId) {
                        $freePackage = Package::where('slug', 'free')->first();
                        $packageId = $freePackage ? $freePackage->id : 1;
                    }

                    $newOrganization = Organization::create([
                        'name' => $validated['organization_name'],
                        'is_active' => true,
                        'package_id' => $packageId,
                    ]);

                    $validated['organization_id'] = $newOrganization->id;
                }

                unset($validated['organization_scenario']);
                unset($validated['organization_name']);
                unset($validated['package_id']);
            }
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('user_updated_successfully'));
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isSystemAdmin() && !$currentUser->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized access to administrator.');
            }
            
            if ($user->organization_id !== $currentUser->organization_id) {
                abort(403, 'Unauthorized access to this user.');
            }
            
            if ($user->organization_id === null || $currentUser->organization_id === null) {
                abort(403, 'Invalid organization access.');
            }
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.users.index')
                    ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
            }
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $organization = $user->organization;
        $user->delete();

        if ($organization) {
            $this->quotaSyncService->decrementUserUsage($organization);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isSystemAdmin() && !$currentUser->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized access to administrator.');
            }
            
            if ($user->organization_id !== $currentUser->organization_id) {
                abort(403, 'Unauthorized access to this user.');
            }
            
            if ($user->organization_id === null || $currentUser->organization_id === null) {
                abort(403, 'Invalid organization access.');
            }
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.users.index')
                    ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
            }
        }

        $validated = $this->validateWithLocale($request, [
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('password_reset_successfully'));
    }

    public function toggleStatus(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isSystemAdmin() && !$currentUser->isAdmin()) {
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized access to administrator.');
            }
            
            if ($user->organization_id !== $currentUser->organization_id) {
                abort(403, 'Unauthorized access to this user.');
            }
            
            if ($user->organization_id === null || $currentUser->organization_id === null) {
                abort(403, 'Invalid organization access.');
            }
            
            if ($user->role === 'admin') {
                return redirect()->route('admin.users.index')
                    ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
            }
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot lock your own account.');
        }

        $organization = $user->organization;
        $wasActive = $user->is_active;
        
        $user->update([
            'is_active' => !$user->is_active
        ]);

        if ($organization) {
            if ($wasActive && !$user->is_active) {
                // User was deactivated
                $this->quotaSyncService->decrementUserUsage($organization);
            } elseif (!$wasActive && $user->is_active) {
                // User was activated
                $this->quotaSyncService->incrementUserUsage($organization);
            }
        }

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }

    public function updatePackage(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can update organization packages.');
        }

        $validated = $this->validateWithLocale($request, [
            'package_id' => 'required|in:free,basic,premium,enterprise',
        ]);

        $organization = $user->organization;
        if (!$organization) {
            return back()->with('error', 'User must belong to an organization.');
        }

        $oldPackage = $organization->package_id;
        $organization->update([
            'package_id' => $validated['package_id'],
        ]);

        \Log::info('Organization package changed', [
            'admin_id' => auth()->id(),
            'organization_id' => $organization->id,
            'old_package' => $oldPackage,
            'new_package' => $validated['package_id'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('package_updated_successfully', ['package' => $validated['package_id']]));
    }
}
