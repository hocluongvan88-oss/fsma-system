@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Create New User</h2>

    {{-- Show user limit warning --}}
    @php
        $currentUser = auth()->user();
        
        $query = \App\Models\User::where('is_active', true);
        
        // Apply organization filter
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'organization_id') && $currentUser->organization_id) {
            $query = $query->where('organization_id', $currentUser->organization_id);
        }
        
        // Exclude system admin from count for non-admin users
        if (!$currentUser->isAdmin()) {
            $query = $query->where('email', '!=', 'admin@fsma204.com');
        }
        
        $activeUserCount = $query->count();
        
        $packageLimits = [
            'free' => 1,
            'basic' => 1,
            'premium' => 3,
            'enterprise' => 999999,
        ];
        
        $userLimit = $currentUser->isAdmin() ? 999999 : ($packageLimits[$currentUser->package_id] ?? 1);
        $remainingUsers = max(0, $userLimit - $activeUserCount);
        
        $packages = \App\Models\Package::visible()->ordered()->get();
    @endphp

    @if($userLimit < 999999)
    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--accent-primary); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem; color: var(--accent-primary);">
            <strong>{{ __('messages.user_limit') }}:</strong> {{ $activeUserCount }}/{{ $userLimit }} {{ __('messages.active_users') }}
            @if($remainingUsers > 0)
                <br>{{ __('messages.can_create_more', ['count' => $remainingUsers]) }}
            @else
                <br><strong style="color: var(--error);">{{ __('messages.limit_reached') }}</strong> {{ __('messages.contact_admin_upgrade') }}
            @endif
        </div>
    </div>
    @else
    {{-- Show unlimited status for Admin users --}}
    <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgb(34, 197, 94); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem; color: rgb(34, 197, 94);">
            <strong>{{ __('messages.current_package') }}:</strong> {{ ucfirst($currentUser->package_id) }} - 
            <strong>{{ __('messages.users') }}:</strong> {{ $activeUserCount }}/Unlimited
        </div>
    </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
        @csrf

        <div class="form-group">
            <label class="form-label" for="username">Username *</label>
            <input type="text" id="username" name="username" class="form-input" value="{{ old('username') }}" required>
            @error('username')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" class="form-input" value="{{ old('full_name') }}" required>
            @error('full_name')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required>
            @error('email')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="role">Role *</label>
            <select id="role" name="role" class="form-select" required>
                <option value="">Select Role</option>
                {{-- Hide Admin option for Manager users --}}
                @if(auth()->user()->isAdmin())
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                @endif
                <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Operator</option>
            </select>
            @if(!auth()->user()->isAdmin())
            <small style="color: var(--text-muted); font-size: 0.75rem;">{{ __('messages.can_only_create_manager_operator') }}</small>
            @endif
            @error('role')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Enhanced Package selection with visual cards for Admin users --}}
        @if(auth()->user()->isAdmin())
        <div class="form-group">
            <label class="form-label">{{ __('messages.package') }} *</label>
            <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-bottom: 1rem;">
                {{ __('messages.select_package_for_user') }}
            </small>
            
            <input type="hidden" name="package_id" id="selected_package_id" value="{{ old('package_id', 'free') }}" required>
            
            <div style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                @foreach($packages as $package)
                <div class="package-card" 
                     data-package-id="{{ $package->id }}"
                     onclick="selectPackage('{{ $package->id }}')"
                     style="cursor: pointer; border: 2px solid var(--border); border-radius: 8px; padding: 1rem; transition: all 0.2s; {{ old('package_id', 'free') === $package->id ? 'border-color: var(--accent-primary); background: rgba(59, 130, 246, 0.05);' : '' }}">
                    
                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="flex: 1;">
                            <h4 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.25rem;">
                                {{ $package->name }}
                                @if(!$package->is_selectable)
                                <span style="background: var(--warning); color: white; padding: 0.125rem 0.375rem; border-radius: 4px; font-size: 0.625rem; margin-left: 0.5rem;">
                                    Not Selectable
                                </span>
                                @endif
                            </h4>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0;">{{ $package->description }}</p>
                        </div>
                        <div class="package-check" style="width: 24px; height: 24px; border: 2px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; {{ old('package_id', 'free') === $package->id ? 'border-color: var(--accent-primary); background: var(--accent-primary);' : '' }}">
                            <span style="color: white; font-size: 0.875rem; {{ old('package_id', 'free') === $package->id ? 'display: block;' : 'display: none;' }}">✓</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; padding: 0.75rem; background: var(--bg); border-radius: 6px; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.125rem; text-transform: uppercase;">CTE Records</div>
                            <div style="font-size: 0.875rem; font-weight: 600;">
                                {{ $package->hasUnlimitedCte() ? '∞' : number_format($package->max_cte_records_monthly) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.125rem; text-transform: uppercase;">Documents</div>
                            <div style="font-size: 0.875rem; font-weight: 600;">
                                {{ $package->hasUnlimitedDocuments() ? '∞' : number_format($package->max_documents) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.625rem; color: var(--text-muted); margin-bottom: 0.125rem; text-transform: uppercase;">Users</div>
                            <div style="font-size: 0.875rem; font-weight: 600;">
                                {{ $package->hasUnlimitedUsers() ? '∞' : $package->max_users }}
                            </div>
                        </div>
                    </div>

                    @if($package->monthly_selling_price || $package->yearly_selling_price)
                    <div style="display: flex; gap: 1rem; font-size: 0.875rem;">
                        @if($package->monthly_selling_price)
                        <div>
                            <span style="color: var(--text-muted);">Monthly:</span>
                            <span style="font-weight: 600;">${{ number_format($package->monthly_selling_price, 2) }}</span>
                        </div>
                        @endif
                        @if($package->yearly_selling_price)
                        <div>
                            <span style="color: var(--text-muted);">Yearly:</span>
                            <span style="font-weight: 600;">${{ number_format($package->yearly_selling_price, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <div style="color: var(--success); font-weight: 600; font-size: 0.875rem;">Free</div>
                    @endif
                </div>
                @endforeach
            </div>
            
            @error('package_id')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>
        @endif

        <div class="form-group">
            <label class="form-label" for="password">Password *</label>
            <input type="password" id="password" name="password" class="form-input" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Minimum 8 characters</small>
            @error('password')
                <span style="color: var(--error); font-size: 0.875rem; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Confirm Password *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem;">
                <span class="form-label" style="margin: 0;">Active</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" {{ $remainingUsers <= 0 && $userLimit < 999999 ? 'disabled' : '' }}>Create User</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- Add JavaScript for package selection interaction --}}
<script>
function selectPackage(packageId) {
    // Update hidden input
    document.getElementById('selected_package_id').value = packageId;
    
    // Update all package cards
    document.querySelectorAll('.package-card').forEach(card => {
        const isSelected = card.dataset.packageId === packageId;
        
        // Update card styling
        if (isSelected) {
            card.style.borderColor = 'var(--accent-primary)';
            card.style.background = 'rgba(59, 130, 246, 0.05)';
        } else {
            card.style.borderColor = 'var(--border)';
            card.style.background = 'transparent';
        }
        
        // Update checkmark
        const check = card.querySelector('.package-check');
        const checkIcon = check.querySelector('span');
        if (isSelected) {
            check.style.borderColor = 'var(--accent-primary)';
            check.style.background = 'var(--accent-primary)';
            checkIcon.style.display = 'block';
        } else {
            check.style.borderColor = 'var(--border)';
            check.style.background = 'transparent';
            checkIcon.style.display = 'none';
        }
    });
}
</script>
@endsection
