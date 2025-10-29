@extends('layouts.app')

@section('title', __('messages.user_management'))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">{{ __('messages.all_users') }}</h2>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">{{ __('messages.add_new_user') }}</a>
    </div>

    {{-- Show current user limit info --}}
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
    @endphp

    @if($userLimit < 999999)
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem;">
            <strong>{{ __('messages.current_package') }}:</strong> {{ ucfirst($currentUser->package_id) }} - 
            <strong>{{ __('messages.users') }}:</strong> {{ $activeUserCount }}/{{ $userLimit }}
            @if($activeUserCount >= $userLimit)
                <span style="color: var(--error); margin-left: 0.5rem;">⚠️ {{ __('messages.limit_reached') }}</span>
            @endif
        </div>
    </div>
    @else
    {{-- Show unlimited status for Admin users --}}
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
        <div style="font-size: 0.875rem;">
            <strong>{{ __('messages.current_package') }}:</strong> {{ ucfirst($currentUser->package_id) }} - 
            <strong>{{ __('messages.users') }}:</strong> {{ $activeUserCount }}/{{ __('messages.unlimited') }}
        </div>
    </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.username') }}</th>
                    <th>{{ __('messages.full_name') }}</th>
                    <th>{{ __('messages.email') }}</th>
                    {{-- Add organization column --}}
                    <th>{{ __('messages.organization') }}</th>
                    <th>{{ __('messages.role') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.last_login') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    {{-- Display organization name --}}
                    <td>
                        @if($user->organization)
                            <span style="font-size: 0.875rem;">{{ $user->organization->name }}</span>
                        @else
                            <span style="color: var(--text-muted); font-size: 0.875rem;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge badge-success">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge badge-error">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td>{{ $user->last_login ? $user->last_login->format('Y-m-d H:i') : __('messages.never') }}</td>
                    <td>
                        @if(auth()->user()->isAdmin() || $user->role !== 'admin')
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                {{ __('messages.edit') }}
                            </a>
                            
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                    {{ $user->is_active ? __('messages.lock') : __('messages.unlock') }}
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('messages.confirm_delete_user') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem; background: var(--error);">
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                            @endif
                        </div>
                        @else
                        <span style="color: var(--text-muted); font-size: 0.75rem;">{{ __('messages.no_access') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_users_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $users->links() }}
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .table-container table {
            font-size: 0.75rem;
        }
        
        .table-container td > div {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .table-container .btn {
            width: 100%;
            min-width: auto;
        }
    }
</style>
@endsection
