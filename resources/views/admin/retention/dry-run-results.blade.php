@extends('layouts.app')

@section('title', 'Dry-Run Results')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Retention Cleanup Dry-Run Results</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                Preview of records that would be deleted without actual execution
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.retention.index') }}" class="btn btn-secondary">Back to Policies</a>
            <form method="POST" action="{{ route('admin.retention.execute', $policy->id) }}" style="display: inline;">
                @csrf
                <input type="hidden" name="dry_run" value="0">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to execute this cleanup? This action cannot be undone.')">
                    Execute Cleanup
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; padding: 1.5rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                Records to Delete
            </h3>
            <p style="font-size: 2.5rem; font-weight: 700; margin: 0;">{{ number_format($results['records_to_delete']) }}</p>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 0.5rem; padding: 1.5rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                Estimated Storage Freed
            </h3>
            <p style="font-size: 2.5rem; font-weight: 700; margin: 0;">{{ $results['storage_freed'] }}</p>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 0.5rem; padding: 1.5rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                Backup Size
            </h3>
            <p style="font-size: 2.5rem; font-weight: 700; margin: 0;">{{ $results['backup_size'] }}</p>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 0.5rem; padding: 1.5rem; color: white;">
            <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.5rem; opacity: 0.9;">
                Estimated Duration
            </h3>
            <p style="font-size: 2.5rem; font-weight: 700; margin: 0;">{{ $results['estimated_duration'] }}</p>
        </div>
    </div>

    <!-- Policy Details -->
    <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem;">
        <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 1rem;">
            Policy Configuration
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">Policy Name:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ $policy->policy_name }}</span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">Data Type:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ ucfirst(str_replace('_', ' ', $policy->data_type)) }}</span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">Retention Period:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ $policy->retention_months }} months</span>
            </div>
            <div>
                <span style="color: var(--text-secondary); font-size: 0.875rem;">Backup Enabled:</span>
                <span style="font-weight: 600; margin-left: 0.5rem;">{{ $policy->backup_before_deletion ? 'Yes' : 'No' }}</span>
            </div>
        </div>
    </div>

    <!-- Records Breakdown -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Records Breakdown by Age</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Age Range</th>
                        <th>Record Count</th>
                        <th>Percentage</th>
                        <th>Estimated Size</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results['breakdown'] as $range)
                    <tr>
                        <td style="font-weight: 500;">{{ $range['label'] }}</td>
                        <td>{{ number_format($range['count']) }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; background: var(--bg-tertiary); border-radius: 9999px; height: 8px; overflow: hidden;">
                                    <div style="background: var(--accent-primary); height: 100%; width: {{ $range['percentage'] }}%;"></div>
                                </div>
                                <span style="font-size: 0.875rem; color: var(--text-secondary);">{{ number_format($range['percentage'], 1) }}%</span>
                            </div>
                        </td>
                        <td style="color: var(--text-secondary);">{{ $range['size'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Comparison with Previous Runs -->
    @if($previousRuns->count() > 0)
    <div>
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Comparison with Previous Runs</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Run Date</th>
                        <th>Records Deleted</th>
                        <th>Storage Freed</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previousRuns as $run)
                    <tr>
                        <td style="font-size: 0.875rem;">{{ $run->executed_at->format('M d, Y H:i') }}</td>
                        <td>{{ number_format($run->records_deleted) }}</td>
                        <td style="color: var(--text-secondary);">{{ $run->storage_freed }}</td>
                        <td style="color: var(--text-secondary);">{{ $run->duration }}</td>
                        <td>
                            @if($run->status === 'success')
                                <span class="badge badge-success">Success</span>
                            @else
                                <span class="badge badge-error">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Warning Notice -->
<div class="card" style="border-left: 4px solid var(--warning);">
    <div style="display: flex; gap: 1rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--warning)" stroke-width="2" style="flex-shrink: 0;">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <div>
            <h3 style="font-weight: 600; margin-bottom: 0.5rem;">Important Notice</h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">
                This is a dry-run preview. No data will be deleted until you execute the cleanup.
            </p>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">
                Please review the results carefully before proceeding with the actual cleanup operation.
            </p>
        </div>
    </div>
</div>
@endsection
