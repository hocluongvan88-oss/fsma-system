@extends('layouts.app')

{{-- Added translation for page title --}}
@section('title', __('messages.edit_user'))

@section('content')
<div class="card">
    {{-- Added translation for heading --}}
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.edit_user') }}: {{ $user->username }}</h2>

    {{-- Show warning if Manager is trying to edit Admin user --}}
    @if(!auth()->user()->isAdmin() && $user->role === 'admin')
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem; color: var(--error);">
            {{-- Added translation for warning message --}}
            <strong>⚠️ {{ __('messages.warning') }}:</strong> {{ __('messages.cannot_edit_admin') }}
        </div>
    </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            {{-- Added translation for labels --}}
            <label class="form-label" for="username">{{ __('messages.username') }} *</label>
            <input type="text" id="username" name="username" class="form-input" value="{{ old('username', $user->username) }}" required>
            @error('username')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="full_name">{{ __('messages.full_name') }} *</label>
            <input type="text" id="full_name" name="full_name" class="form-input" value="{{ old('full_name', $user->full_name) }}" required>
            @error('full_name')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">{{ __('messages.email') }} *</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="role">{{ __('messages.role') }} *</label>
            <select id="role" name="role" class="form-select" required>
                {{-- Hide Admin option for Manager users --}}
                @if(auth()->user()->isAdmin())
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                @endif
                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Operator</option>
            </select>
            @if(!auth()->user()->isAdmin())
            {{-- Added translation for role restriction message --}}
            <small style="color: var(--text-muted); font-size: 0.75rem;">{{ __('messages.can_only_edit_manager_operator') }}</small>
            @endif
            @error('role')
                <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Add organization assignment section for admin users --}}
        @if(auth()->user()->isAdmin())
        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; margin: 1.5rem 0;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.organization_assignment') }}</h3>
            
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="organization_scenario" value="existing" 
                        {{ old('organization_scenario', 'existing') === 'existing' ? 'checked' : '' }}
                        onchange="toggleOrganizationScenario()" style="width: 1.25rem; height: 1.25rem;">
                    <span>{{ __('messages.assign_to_existing_organization') }}</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="radio" name="organization_scenario" value="new" 
                        {{ old('organization_scenario') === 'new' ? 'checked' : '' }}
                        onchange="toggleOrganizationScenario()" style="width: 1.25rem; height: 1.25rem;">
                    <span>{{ __('messages.create_new_organization') }}</span>
                </label>
            </div>

            {{-- Existing Organization Section --}}
            <div id="existing-org-section" style="display: {{ old('organization_scenario', 'existing') === 'existing' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label class="form-label" for="organization_id">{{ __('messages.organization') }} *</label>
                    <select id="organization_id" name="organization_id" class="form-select">
                        <option value="">{{ __('messages.select_organization') }}</option>
                        @foreach($organizations as $org)
                        <option value="{{ $org->id }}" 
                            {{ old('organization_id', $user->organization_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                    @error('organization_id')
                        <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- New Organization Section --}}
            <div id="new-org-section" style="display: {{ old('organization_scenario') === 'new' ? 'block' : 'none' }};">
                <div class="form-group">
                    <label class="form-label" for="organization_name">{{ __('messages.organization_name') }} *</label>
                    <input type="text" id="organization_name" name="organization_name" class="form-input" 
                        value="{{ old('organization_name') }}" placeholder="Enter new organization name">
                    @error('organization_name')
                        <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="package_id">{{ __('messages.service_package') }} *</label>
                    <select id="package_id" name="package_id" class="form-select">
                        <option value="">{{ __('messages.select_package') }}</option>
                        @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>{{ $package->name }} - {{ $package->description }}</option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        @endif

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem;">
                <span class="form-label" style="margin: 0;">{{ __('messages.active') }}</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            {{-- Added translations for form buttons --}}
            <button type="submit" class="btn btn-primary">{{ __('messages.update_user') }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>

{{-- Only Admin can manage packages --}}
@if(auth()->user()->isAdmin())
<div class="card" style="margin-top: 1.5rem;">
    {{-- Added translation for package management section --}}
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.package_management') }}</h3>
    
    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.current_package') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600; text-transform: uppercase;">{{ $user->package_id }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.cte_records_month') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">{{ $user->max_cte_records_monthly == 0 ? __('messages.unlimited') : number_format($user->max_cte_records_monthly) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.max_documents') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">{{ $user->max_documents == 0 ? __('messages.unlimited') : number_format($user->max_documents) }}</div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.max_users') }}</div>
                <div style="font-size: 1.25rem; font-weight: 600;">{{ $user->max_users == 0 ? __('messages.unlimited') : number_format($user->max_users) }}</div>
            </div>
        </div>
    </div>
    
    <form action="{{ route('admin.users.update-package', $user) }}" method="POST">
        @csrf
        {{-- Changed from @method('PATCH') to match POST route definition --}}

        <div class="form-group">
            <label class="form-label" for="package_id">{{ __('messages.change_package') }} *</label>
            <select id="package_id" name="package_id" class="form-select" required>
                <option value="free" {{ $user->package_id === 'free' ? 'selected' : '' }}>
                    Free Tier - 50 CTE/month, 1 doc, 1 user
                </option>
                <option value="basic" {{ $user->package_id === 'basic' ? 'selected' : '' }}>
                    Basic - 500 CTE/month, 10 docs, 1 user
                </option>
                <option value="premium" {{ $user->package_id === 'premium' ? 'selected' : '' }}>
                    Premium - 2,500 CTE/month, Unlimited docs, 3 users
                </option>
                <option value="enterprise" {{ $user->package_id === 'enterprise' ? 'selected' : '' }}>
                    Enterprise - 5,000+ CTE/month, Unlimited docs, Unlimited users
                </option>
            </select>
            <small style="color: var(--text-muted); font-size: 0.75rem;">
                {{ __('messages.changing_package_updates_limits') }}
            </small>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.update_package') }}</button>
    </form>
</div>
@endif

{{-- Manager cannot reset Admin passwords --}}
@if(auth()->user()->isAdmin() || $user->role !== 'admin')
<div class="card" style="margin-top: 1.5rem;">
    {{-- Added translation for reset password section --}}
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.reset_password') }}</h3>
    
    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label class="form-label" for="password">{{ __('messages.new_password') }} *</label>
            <input type="password" id="password" name="password" class="form-input" required>
            <small style="color: var(--text-muted); font-size: 0.75rem;">{{ __('messages.minimum_8_characters') }}</small>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">{{ __('messages.confirm_new_password') }} *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.reset_password') }}</button>
    </form>
</div>
@endif

<script>
function toggleOrganizationScenario() {
    const scenario = document.querySelector('input[name="organization_scenario"]:checked').value;
    const existingSection = document.getElementById('existing-org-section');
    const newSection = document.getElementById('new-org-section');
    
    if (scenario === 'existing') {
        existingSection.style.display = 'block';
        newSection.style.display = 'none';
        document.getElementById('organization_id').required = true;
        document.getElementById('organization_name').required = false;
        document.getElementById('package_id').required = false;
    } else {
        existingSection.style.display = 'none';
        newSection.style.display = 'block';
        document.getElementById('organization_id').required = false;
        document.getElementById('organization_name').required = true;
        document.getElementById('package_id').required = true;
    }
}
</script>
@endsection
