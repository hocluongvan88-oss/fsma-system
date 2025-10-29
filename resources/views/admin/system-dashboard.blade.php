@extends('layouts.app')

@section('title', __('messages.system_admin_dashboard'))

@section('content')
{{-- System Admin Header --}}
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 2rem; border: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
        <div>
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">{{ __('messages.system_admin_dashboard') }}</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ __('messages.global_overview') }}</div>
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('admin.packages.index') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                {{ __('messages.manage_packages') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                {{ __('messages.view_all_users') }}
            </a>
        </div>
    </div>
</div>

{{-- System Statistics Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    {{-- Total Organizations --}}
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">{{ __('messages.total_organizations') }}</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ number_format($total_organizations ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Total Users --}}
    <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">{{ __('messages.total_system_users') }}</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ number_format($total_users ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">{{ __('messages.active_users_30d') }}</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ number_format($active_users ?? 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Total CTE Records --}}
    <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">{{ __('messages.total_cte_records') }}</div>
                <div style="font-size: 2rem; font-weight: 700;">{{ number_format($total_cte_records ?? 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Charts and Analytics --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    {{-- Package Distribution --}}
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            {{ __('messages.package_distribution') }}
        </h3>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($package_distribution ?? [] as $package)
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 500;">{{ $package->name }}</span>
                    <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ $package->organizations_count }} {{ __('messages.organizations') }}</span>
                </div>
                <div style="width: 100%; height: 8px; background: var(--bg-tertiary); border-radius: 4px; overflow: hidden;">
                    <div style="width: {{ ($package->organizations_count / max($total_organizations, 1)) * 100 }}%; height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);"></div>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">{{ __('messages.no_data') }}</div>
            @endforelse
        </div>
    </div>

    {{-- System Health --}}
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            {{ __('messages.system_health') }}
        </h3>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.database_size') }}</div>
                    <div style="font-size: 1.5rem; font-weight: 600;">{{ $database_size ?? 'N/A' }}</div>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(102, 126, 234, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.storage_used') }}</div>
                    <div style="font-size: 1.5rem; font-weight: 600;">{{ $storage_used ?? 'N/A' }}</div>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(67, 233, 123, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.error_rate') }}</div>
                    <div style="font-size: 1.5rem; font-weight: 600;">{{ $error_rate ?? 0 }}%</div>
                </div>
                <div style="width: 48px; height: 48px; background: rgba(245, 93, 108, 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Organizations and Activity --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 1.5rem;">
    {{-- Recent Organizations --}}
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.125rem; font-weight: 600;">{{ __('messages.recent_organizations') }}</h3>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                {{ __('messages.view_all_organizations') }}
            </a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.organization') }}</th>
                        <th>{{ __('messages.package') }}</th>
                        <th>{{ __('messages.users') }}</th>
                        <th>{{ __('messages.created') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_organizations ?? [] as $org)
                    <tr>
                        <td style="font-weight: 500;">{{ $org->name }}</td>
                        <td>
                            <span class="badge badge-info">{{ $org->package->name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $org->users_count ?? 0 }}</td>
                        <td style="color: var(--text-secondary); font-size: 0.875rem;">
                            {{ $org->created_at?->diffForHumans() ?? 'N/A' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Organizations by Usage --}}
    <div class="card">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.top_organizations_by_usage') }}</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.organization') }}</th>
                        <th>{{ __('messages.cte_records_count') }}</th>
                        <th>{{ __('messages.documents') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($top_organizations ?? [] as $org)
                    <tr>
                        <td style="font-weight: 500;">{{ $org->name }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span>{{ number_format($org->cte_count ?? 0) }}</span>
                                <div style="flex: 1; height: 4px; background: var(--bg-tertiary); border-radius: 2px; max-width: 100px;">
                                    <div style="width: {{ min((($org->cte_count ?? 0) / 1000) * 100, 100) }}%; height: 100%; background: var(--accent-primary); border-radius: 2px;"></div>
                                </div>
                            </div>
                        </td>
                        <td>{{ number_format($org->document_count ?? 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
