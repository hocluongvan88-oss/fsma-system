@extends('layouts.app')

@section('title', __('messages.compliance_dashboard'))

@section('content')
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ __('messages.fsma_204_compliance_dashboard') }}</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                Real-time compliance monitoring and FSMA 204 requirement tracking
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.compliance.audit-report') }}" class="btn btn-secondary">
                Generate Audit Report
            </a>
            <a href="{{ route('admin.compliance.recommendations') }}" class="btn btn-primary">
                View Recommendations
            </a>
        </div>
    </div>

    <!-- Compliance Score -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.75rem; padding: 2rem; margin-bottom: 2rem; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                    Overall Compliance Score
                </h3>
                <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                    <span style="font-size: 3.5rem; font-weight: 700;">{{ number_format($complianceScore, 1) }}%</span>
                    @if($complianceScore >= 90)
                        <span class="badge badge-success" style="background: rgba(255, 255, 255, 0.2); color: white;">Excellent</span>
                    @elseif($complianceScore >= 75)
                        <span class="badge badge-info" style="background: rgba(255, 255, 255, 0.2); color: white;">Good</span>
                    @elseif($complianceScore >= 60)
                        <span class="badge badge-warning" style="background: rgba(255, 255, 255, 0.2); color: white;">Needs Improvement</span>
                    @else
                        <span class="badge badge-error" style="background: rgba(255, 255, 255, 0.2); color: white;">Critical</span>
                    @endif
                </div>
                <p style="margin-top: 0.75rem; opacity: 0.9; font-size: 0.875rem;">
                    Last updated: {{ now()->format('M d, Y H:i') }}
                </p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                    {{ $metrics['total_policies'] }}/{{ $metrics['required_policies'] }}
                </div>
                <div style="opacity: 0.9; font-size: 0.875rem;">Active Policies</div>
            </div>
        </div>
    </div>

    <!-- FSMA 204 Requirements Checklist -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">FSMA 204 Requirements Checklist</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            @foreach($requirements as $requirement)
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; margin: 0;">{{ $requirement['title'] }}</h4>
                    @if($requirement['status'] === 'compliant')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    @elseif($requirement['status'] === 'partial')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--error)" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    @endif
                </div>
                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.75rem;">
                    {{ $requirement['description'] }}
                </p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        Section {{ $requirement['section'] }}
                    </span>
                    @if($requirement['status'] === 'compliant')
                        <span class="badge badge-success">Compliant</span>
                    @elseif($requirement['status'] === 'partial')
                        <span class="badge badge-warning">Partial</span>
                    @else
                        <span class="badge badge-error">Non-Compliant</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Policy Coverage Heatmap -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Data Retention Policy Coverage</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem;">
            @foreach($policyCoverage as $dataType => $coverage)
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; text-align: center;">
                <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">
                    {{ str_replace('_', ' ', $dataType) }}
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: {{ $coverage['has_policy'] ? 'var(--success)' : 'var(--error)' }};">
                    {{ $coverage['retention_months'] ?? 'N/A' }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    {{ $coverage['has_policy'] ? 'months' : 'No Policy' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Audit Activity -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">Recent Audit Activity</h3>
            <a href="{{ route('admin.audit.real-time') }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">
                View Real-Time Dashboard
            </a>
        </div>
        <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; max-height: 400px; overflow-y: auto;">
            @forelse($recentActivity as $activity)
            <div style="display: flex; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                <div style="flex-shrink: 0; width: 40px; height: 40px; border-radius: 50%; background: var(--bg-secondary); display: flex; align-items: center; justify-content: center;">
                    @if($activity->action === 'retention_executed')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-primary)" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    @elseif($activity->action === 'policy_created')
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    @else
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    @endif
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 500; margin-bottom: 0.25rem;">{{ $activity->description }}</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">
                        by {{ $activity->user->full_name }} • {{ $activity->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
                No recent activity
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Alerts Section -->
@if($alerts->count() > 0)
<div class="card" style="border-left: 4px solid var(--error);">
    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--error);">
        Compliance Alerts ({{ $alerts->count() }})
    </h3>
    @foreach($alerts as $alert)
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 0.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $alert['title'] }}</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ $alert['message'] }}</div>
            </div>
            <span class="badge badge-error">{{ $alert['severity'] }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
