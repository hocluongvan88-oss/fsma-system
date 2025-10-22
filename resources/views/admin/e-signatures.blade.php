@extends('layouts.app')

@section('title', 'E-Signatures')

@section('content')
<div class="card">
    <!-- Header with title and create button in single row -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Electronic Signatures (21 CFR Part 11)</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('e-signatures.performance-dashboard') }}" class="btn btn-secondary" style="text-decoration: none;">
                📊 Performance
            </a>
            <button class="btn btn-primary" onclick="openSignatureModal('document', 0, 'sign')">
                + Create Signature
            </button>
        </div>
    </div>
    
    <!-- Toolbar with filter fields in single row, matching reference layout -->
    <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
        <form method="GET" action="{{ route('e-signatures.index') }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 150px;">
                <label for="search" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Search</label>
                <input type="text" name="search" id="search" class="form-input" 
                       placeholder="User, record type, action..." value="{{ request('search') }}" style="margin: 0;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="user_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">User</label>
                <select name="user_id" id="user_id" class="form-select" style="margin: 0;">
                    <option value="">All Users</option>
                    @foreach($users ?? [] as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="date_from" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-input" value="{{ request('date_from') }}" style="margin: 0;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="date_to" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-input" value="{{ request('date_to') }}" style="margin: 0;">
            </div>
            
            <div style="flex: 1; min-width: 150px;">
                <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-secondary); font-size: 0.875rem;">Status</label>
                <select name="status" id="status" class="form-select" style="margin: 0;">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="margin: 0;">Filter</button>
                <a href="{{ route('e-signatures.index') }}" class="btn btn-secondary" style="margin: 0; text-decoration: none;">Reset</a>
            </div>
        </form>
    </div>
    
    <!-- Table container with proper scrolling -->
    <div class="table-container" style="overflow-x: auto; margin-bottom: 1rem;">
        <table>
            <thead>
                <tr>
                    <th>Signed At</th>
                    <th>User</th>
                    <th>Record Type</th>
                    <th>Record ID</th>
                    <th>Action</th>
                    <th>Meaning</th>
                    <th>Status</th>
                    <th>Validity</th>
                    <th>IP Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signatures as $signature)
                <tr>
                    <td>{{ $signature->signed_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $signature->user->full_name }}</td>
                    <td><code style="font-size: 0.75rem;">{{ $signature->record_type }}</code></td>
                    <td>{{ $signature->record_id }}</td>
                    <td>
                        <span class="badge badge-info">{{ $signature->action }}</span>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                        {{ $signature->meaning_of_signature ?? '-' }}
                    </td>
                    <td>
                        @if($signature->is_revoked)
                            <span class="badge badge-error">Revoked</span>
                        @elseif($signature->expiration_status === 'expired')
                            <span class="badge badge-warning">Expired</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td style="font-size: 0.75rem;">
                        @if($signature->signature_valid_until)
                            {{ $signature->signature_valid_until->format('Y-m-d') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="font-size: 0.75rem; color: var(--text-muted);">{{ $signature->ip_address }}</td>
                    <td>
                        <a href="{{ route('e-signatures.show', $signature) }}" class="btn btn-sm" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">No e-signatures recorded</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination in normal flow -->
    {{ $signatures->links() }}
</div>

@include('admin.e-signatures.sign-modal')

<style>
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.btn-secondary {
    background: var(--bg-tertiary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: var(--bg-secondary);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-info {
    background-color: rgba(59, 130, 246, 0.1);
    color: var(--accent-primary);
}

.badge-success {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.badge-error {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--error);
}

.badge-warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: var(--warning);
}

.form-input, .form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    font-size: 0.875rem;
    font-size: 16px;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: var(--accent-primary);
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    border: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background: var(--accent-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-hover);
}
</style>
@endsection
