<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Package;

class UserController extends BaseController
{
    public function index()
    {
        $currentUser = auth()->user();
        $query = User::orderBy('created_at', 'desc');
        
        if (Schema::hasColumn('users', 'organization_id')) {
            if ($currentUser->organization_id) {
                $query->where('organization_id', $currentUser->organization_id);
            } else {
                if (!$currentUser->isAdmin()) {
                    $query->where('id', $currentUser->id);
                } else {
                    $query->whereNull('organization_id');
                }
            }
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
        return view('users.create');
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        $query = User::where('is_active', true);
        
        if (Schema::hasColumn('users', 'organization_id')) {
            if ($currentUser->organization_id) {
                $query->where('organization_id', $currentUser->organization_id);
            } else {
                if (!$currentUser->isAdmin()) {
                    $query->where('id', $currentUser->id);
                } else {
                    $query->whereNull('organization_id');
                }
            }
        }
        
        if (!$currentUser->isAdmin()) {
            $query->where('email', '!=', 'admin@fsma204.com');
        }
        
        $activeUserCount = $query->count();
        
        $packageLimits = [
            'free' => 1,
            'basic' => 1,
            'premium' => 3,
            'enterprise' => 999999,
        ];
        
        $userLimit = $currentUser->isAdmin() ? 999999 : ($packageLimits[$currentUser->package_id] ?? 1);
        
        if ($activeUserCount >= $userLimit && $userLimit < 999999) {
            return back()->withInput()
                ->with('error', $this->getLocalizedErrorMessage('user_limit_reached', ['limit' => $userLimit]));
        }

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
        ];

        if ($currentUser->isAdmin()) {
            $validationRules['package_id'] = 'required|exists:packages,id';
        }

        $validated = $this->validateWithLocale($request, $validationRules);

        if (!$currentUser->isAdmin() && $validated['role'] === 'admin') {
            return back()->withInput()
                ->with('error', $this->getLocalizedErrorMessage('no_permission_create_admin'));
        }

        if ($currentUser->isAdmin() && isset($validated['package_id'])) {
            $package = Package::find($validated['package_id']);
            if ($package) {
                $validated['max_cte_records_monthly'] = $package->max_cte_records_monthly;
                $validated['max_documents'] = $package->max_documents;
                $validated['max_users'] = $package->max_users;
            }
        } else {
            $validated['package_id'] = $currentUser->package_id;
            $validated['max_cte_records_monthly'] = $currentUser->max_cte_records_monthly;
            $validated['max_documents'] = $currentUser->max_documents;
            $validated['max_users'] = $currentUser->max_users;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active');
        
        if ($currentUser->organization_id) {
            $validated['organization_id'] = $currentUser->organization_id;
        } else {
            if ($currentUser->isAdmin()) {
                $validated['organization_id'] = time() . rand(1000, 9999);
            } else {
                $validated['organization_id'] = $currentUser->organization_id;
            }
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('user_created_successfully'));
    }

    public function edit(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isAdmin() && $user->email === 'admin@fsma204.com') {
            abort(403, 'Unauthorized access to system administrator.');
        }
        
        if ($user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this user.');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isAdmin() && $user->email === 'admin@fsma204.com') {
            abort(403, 'Unauthorized access to system administrator.');
        }
        
        if ($user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this user.');
        }
        
        if (!$currentUser->isAdmin() && $user->role === 'admin') {
            return back()
                ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
        }

        $allowedRoles = $currentUser->isAdmin() 
            ? ['admin', 'manager', 'operator'] 
            : ['manager', 'operator'];

        $validated = $this->validateWithLocale($request, [
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'full_name' => 'required|string|max:255',
            'role' => ['required', Rule::in($allowedRoles)],
            'is_active' => 'boolean',
        ]);

        if (!$currentUser->isAdmin() && $validated['role'] === 'admin') {
            return back()
                ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
        }

        $validated['is_active'] = $request->has('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('user_updated_successfully'));
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isAdmin() && $user->email === 'admin@fsma204.com') {
            abort(403, 'Unauthorized access to system administrator.');
        }
        
        if ($user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this user.');
        }
        
        if (!$currentUser->isAdmin() && $user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        if (!$currentUser->isAdmin() && $user->email === 'admin@fsma204.com') {
            abort(403, 'Unauthorized access to system administrator.');
        }
        
        if ($user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this user.');
        }
        
        if (!$currentUser->isAdmin() && $user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
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
        
        if (!$currentUser->isAdmin() && $user->email === 'admin@fsma204.com') {
            abort(403, 'Unauthorized access to system administrator.');
        }
        
        if ($user->organization_id !== $currentUser->organization_id) {
            abort(403, 'Unauthorized access to this user.');
        }
        
        if (!$currentUser->isAdmin() && $user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', $this->getLocalizedErrorMessage('cannot_edit_admin'));
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot lock your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }

    public function updatePackage(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', $this->getLocalizedErrorMessage('no_permission_create_admin'));
        }

        $validated = $this->validateWithLocale($request, [
            'package_id' => 'required|in:free,basic,premium,enterprise',
        ]);

        $packageLimits = [
            'free' => [
                'max_cte_records_monthly' => 50,
                'max_documents' => 1,
                'max_users' => 1,
            ],
            'basic' => [
                'max_cte_records_monthly' => 500,
                'max_documents' => 10,
                'max_users' => 1,
            ],
            'premium' => [
                'max_cte_records_monthly' => 2500,
                'max_documents' => 0,
                'max_users' => 3,
            ],
            'enterprise' => [
                'max_cte_records_monthly' => 5000,
                'max_documents' => 0,
                'max_users' => 0,
            ],
        ];

        $limits = $packageLimits[$validated['package_id']];

        $user->update([
            'package_id' => $validated['package_id'],
            'max_cte_records_monthly' => $limits['max_cte_records_monthly'],
            'max_documents' => $limits['max_documents'],
            'max_users' => $limits['max_users'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', $this->getLocalizedSuccessMessage('package_updated_successfully', ['package' => $validated['package_id']]));
    }
}
