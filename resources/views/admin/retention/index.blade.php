@extends('layouts.app')

@section('title', __('messages.data_retention_policies'))

@section('content')
<div class="card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ __('messages.data_retention_policies') }}</h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">{{ __('messages.retention_description') }}</p>
        </div>
        <button class="btn btn-primary" onclick="openCreatePolicyModal()">
            + {{ __('messages.create_policy') }}
        </button>
    </div>

    @if ($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
            <ul style="margin-left: 1.5rem; color: var(--error);">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; color: var(--success);">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        @foreach ($stats as $dataType => $stat)
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.75rem;">{{ $stat['policy_name'] }}</h3>
                <div style="space-y: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.retention') }}:</span>
                        <span style="font-weight: 600;">{{ $stat['retention_months'] }} {{ __('messages.months') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.records_to_delete') }}:</span>
                        <span style="color: var(--error); font-weight: 600;">{{ $stat['records_to_delete'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.last_cleanup') }}:</span>
                        <span style="font-size: 0.875rem;">
                            @if ($stat['last_cleanup'])
                                {{ $stat['last_cleanup']->format('M d, Y H:i') }}
                            @else
                                <span style="color: var(--text-muted);">{{ __('messages.never') }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Policies Table -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.active_policies') }}</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.policy_name') }}</th>
                        <th>{{ __('messages.data_type') }}</th>
                        <th>{{ __('messages.retention') }}</th>
                        <th>{{ __('messages.backup') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.last_executed') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($policies as $policy)
                        <tr>
                            <td style="font-weight: 500;">{{ $policy->policy_name }}</td>
                            <td style="color: var(--text-secondary);">{{ str_replace('_', ' ', ucfirst($policy->data_type)) }}</td>
                            <td>{{ $policy->retention_months }} {{ __('messages.months') }}</td>
                            <td>
                                @if ($policy->backup_before_deletion)
                                    <span class="badge badge-success">{{ __('messages.enabled') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.disabled') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($policy->is_active)
                                    <span class="badge badge-success">{{ __('messages.active') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td style="font-size: 0.875rem; color: var(--text-secondary);">
                                @if ($policy->last_executed_at)
                                    {{ $policy->last_executed_at->format('M d, Y H:i') }}
                                @else
                                    <span style="color: var(--text-muted);">{{ __('messages.never') }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button onclick="executeCleanup({{ $policy->id }})" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                        {{ __('messages.execute') }}
                                    </button>
                                    <button onclick="editPolicy({{ $policy->id }})" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">
                                        {{ __('messages.edit') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                {{ __('messages.no_policies_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Logs -->
    <div>
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.recent_cleanup_logs') }}</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.data_type') }}</th>
                        <th>{{ __('messages.deleted') }}</th>
                        <th>{{ __('messages.backed_up') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.executed_at') }}</th>
                        <th>{{ __('messages.duration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td style="font-weight: 500;">{{ str_replace('_', ' ', ucfirst($log->data_type)) }}</td>
                            <td>{{ $log->records_deleted }}</td>
                            <td>{{ $log->records_backed_up }}</td>
                            <td>
                                @if ($log->status === 'success')
                                    <span class="badge badge-success">{{ __('messages.success') }}</span>
                                @elseif ($log->status === 'failed')
                                    <span class="badge badge-error">{{ __('messages.failed') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('messages.partial') }}</span>
                                @endif
                            </td>
                            <td style="font-size: 0.875rem;">{{ $log->executed_at->format('M d, Y H:i:s') }}</td>
                            <td style="font-size: 0.875rem; color: var(--text-secondary);">{{ $log->duration_seconds }}s</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                                {{ __('messages.no_cleanup_logs') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Policy Modal -->
<div id="policyModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitle" style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.create_retention_policy') }}</h3>
        
        <form id="policyForm" method="POST" action="{{ route('admin.retention.store') }}">
            @csrf
            <input type="hidden" id="policyId" name="policy_id">
            <input type="hidden" id="methodField" name="_method" value="POST">
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.policy_name') }}</label>
                <input type="text" id="policyName" name="policy_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.data_type') }}</label>
                <select id="dataType" name="data_type" class="form-select" required>
                    <option value="">{{ __('messages.select_data_type') }}</option>
                    <option value="trace_records">{{ __('messages.trace_records') }}</option>
                    <option value="cte_events">{{ __('messages.cte_events') }}</option>
                    <option value="audit_logs">{{ __('messages.audit_logs') }}</option>
                    <option value="e_signatures">{{ __('messages.e_signatures') }}</option>
                    <option value="error_logs">{{ __('messages.error_logs') }}</option>
                    <option value="notifications">{{ __('messages.notifications') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.retention_months') }}</label>
                <input type="number" id="retentionMonths" name="retention_months" class="form-input" min="0" max="120" required>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="backupBeforeDeletion" name="backup_before_deletion" value="1" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span style="color: var(--text-secondary);">{{ __('messages.backup_before_deletion') }}</span>
                </label>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.description') }}</label>
                <textarea id="description" name="description" class="form-textarea" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closePolicyModal()" class="btn btn-secondary">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('messages.save_policy') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Execute Cleanup Modal -->
<div id="executeModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 400px;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.execute_cleanup') }}</h3>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">{{ __('messages.choose_execution_mode') }}</p>
        
        <form id="executeForm" method="POST" data-base-url="{{ url('admin/retention') }}">
            @csrf
            <input type="hidden" id="executePolicyId" name="policy_id">
            
            <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
                <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <input type="radio" name="dry_run" value="0" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span>{{ __('messages.execute') }}</span>
                </label>
                <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
                    <input type="radio" name="dry_run" value="1" style="width: 18px; height: 18px; margin-right: 0.75rem;">
                    <span>{{ __('messages.dry_run') }}</span>
                </label>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeExecuteModal()" class="btn btn-secondary">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('messages.proceed') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreatePolicyModal() {
    document.getElementById('modalTitle').textContent = '{{ __('messages.create_retention_policy') }}';
    document.getElementById('policyForm').action = '{{ route("admin.retention.store") }}';
    document.getElementById('methodField').value = 'POST';
    document.getElementById('policyId').value = '';
    document.getElementById('policyName').value = '';
    document.getElementById('dataType').value = '';
    document.getElementById('retentionMonths').value = '';
    document.getElementById('backupBeforeDeletion').checked = true;
    document.getElementById('description').value = '';
    document.getElementById('policyModal').style.display = 'flex';
}

function editPolicy(policyId) {
    fetch(`{{ route('admin.retention.edit', '') }}/${policyId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = '{{ __('messages.edit_retention_policy') }}';
            document.getElementById('policyForm').action = `{{ route('admin.retention.update', '') }}/${policyId}`;
            document.getElementById('methodField').value = 'PUT';
            
            document.getElementById('policyId').value = policyId;
            document.getElementById('policyName').value = data.policy_name;
            document.getElementById('dataType').value = data.data_type;
            document.getElementById('retentionMonths').value = data.retention_months;
            document.getElementById('backupBeforeDeletion').checked = data.backup_before_deletion;
            document.getElementById('description').value = data.description || '';
            document.getElementById('policyModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error fetching policy:', error);
            alert('Failed to load policy data');
        });
}

function closePolicyModal() {
    document.getElementById('policyModal').style.display = 'none';
}

function executeCleanup(policyId) {
    const baseUrl = document.getElementById('executeForm').dataset.baseUrl;
    const executeUrl = `${baseUrl}/${policyId}/execute`;
    
    if (!executeUrl.includes('/execute')) {
        console.error('[v0] Invalid execute URL:', executeUrl);
        alert('Error: Invalid policy ID. Please try again.');
        return;
    }
    
    document.getElementById('executeForm').action = executeUrl;
    document.getElementById('executePolicyId').value = policyId;
    
    document.getElementById('executeModal').style.display = 'flex';
}

function closeExecuteModal() {
    document.getElementById('executeModal').style.display = 'none';
}

document.getElementById('policyModal').addEventListener('click', function(e) {
    if (e.target === this) closePolicyModal();
});

document.getElementById('executeModal').addEventListener('click', function(e) {
    if (e.target === this) closeExecuteModal();
});

document.getElementById('executeForm').addEventListener('submit', function(e) {
    const action = this.action;
    
    if (!action || !action.includes('/execute')) {
        e.preventDefault();
        console.error('[v0] Form action not properly set:', action);
        alert('Error: Form configuration error. Please close and try again.');
        return false;
    }
});
</script>
@endsection
