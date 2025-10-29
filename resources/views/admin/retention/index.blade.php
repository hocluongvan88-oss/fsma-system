@extends('layouts.app')
@section('title', __('messages.data_retention_policies'))
@section('content')
<div class="card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
      <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Data Retention Policies</h2>
      <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">Manage data retention policies</p>
    </div>
    <button class="btn btn-primary" onclick="openCreatePolicyModal()">+ Create Policy</button>
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

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    @foreach ($stats as $dataType => $stat)
      <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem;">
        <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.75rem;">{{ $stat['policy_name'] }}</h3>
        <div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="color: var(--text-secondary); font-size: 0.875rem;">Retention:</span>
            <span style="font-weight: 600;">{{ $stat['retention_months'] }} months</span>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
            <span style="color: var(--text-secondary); font-size: 0.875rem;">Records to delete:</span>
            <span style="color: var(--error); font-weight: 600;">{{ $stat['records_to_delete'] }}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--text-secondary); font-size: 0.875rem;">Last cleanup:</span>
            <span style="font-size: 0.875rem;">
              @if ($stat['last_cleanup'])
                {{ $stat['last_cleanup']->format('M d, Y H:i') }}
              @else
                Never
              @endif
            </span>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div style="margin-bottom: 2rem;">
    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Active Policies</h3>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Policy Name</th>
            <th>Data Type</th>
            <th>Retention</th>
            <th>Backup</th>
            <th>Status</th>
            <th>Last Executed</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($policies as $policy)
            <tr>
              <td style="font-weight: 500;">{{ $policy->policy_name }}</td>
              <td style="color: var(--text-secondary);">{{ str_replace('_', ' ', ucfirst($policy->data_type)) }}</td>
              <td>{{ $policy->retention_months }} months</td>
              <td>
                @if ($policy->backup_before_deletion)
                  <span class="badge badge-success">Enabled</span>
                @else
                  <span class="badge badge-warning">Disabled</span>
                @endif
              </td>
              <td>
                @if ($policy->is_active)
                  <span class="badge badge-success">Active</span>
                @else
                  <span class="badge badge-warning">Inactive</span>
                @endif
              </td>
              <td style="font-size: 0.875rem; color: var(--text-secondary);">
                @if ($policy->last_executed_at)
                  {{ $policy->last_executed_at->format('M d, Y H:i') }}
                @else
                  Never
                @endif
              </td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <button onclick="executeCleanup({{ $policy->id }})" class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">Execute</button>
                  <button onclick="editPolicy({{ $policy->id }})" class="btn btn-secondary" style="padding: 0.5rem 0.75rem; font-size: 0.75rem;">Edit</button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No policies found</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Recent Cleanup Logs</h3>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Data Type</th>
            <th>Deleted</th>
            <th>Backed Up</th>
            <th>Status</th>
            <th>Executed At</th>
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
                  <span class="badge badge-success">Success</span>
                @elseif ($log->status === 'failed')
                  <span class="badge badge-error">Failed</span>
                @else
                  <span class="badge badge-warning">Partial</span>
                @endif
              </td>
              <td style="font-size: 0.875rem;">{{ $log->executed_at->format('M d, Y H:i:s') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No logs found</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="policyModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
  <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;">
    <h3 id="modalTitle" style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem;">Create Retention Policy</h3>
    <form id="policyForm" method="POST" action="{{ route('admin.retention.store') }}">
      @csrf
      <input type="hidden" id="policyId" name="policy_id">
      <input type="hidden" id="methodField" name="_method" value="POST">
      <div class="form-group">
        <label class="form-label">Policy Name</label>
        <input type="text" id="policyName" name="policy_name" class="form-input" required>
      </div>
      <div class="form-group">
        <label class="form-label">Data Type</label>
        <select id="dataType" name="data_type" class="form-select" required>
          <option value="">Select data type</option>
          <option value="error_logs">Error Logs</option>
          <option value="notifications">Notifications</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Retention Months</label>
        <input type="number" id="retentionMonths" name="retention_months" class="form-input" min="0" max="120" required>
      </div>
      <div class="form-group">
        <label style="display: flex; align-items: center; cursor: pointer;">
          <input type="checkbox" id="backupBeforeDeletion" name="backup_before_deletion" value="1" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
          <span style="color: var(--text-secondary);">Backup before deletion</span>
        </label>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="description" name="description" class="form-textarea" rows="3"></textarea>
      </div>
      <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
        <button type="button" onclick="closePolicyModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Policy</button>
      </div>
    </form>
  </div>
</div>

<div id="executeModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
  <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 400px;">
    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">Execute Cleanup</h3>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Choose execution mode</p>
    <form id="executeForm" method="POST" action="">
      @csrf
      <input type="hidden" id="executePolicyId" name="policy_id">
      <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
        <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
          <input type="radio" name="dry_run" value="0" checked style="width: 18px; height: 18px; margin-right: 0.75rem;">
          <span>Execute</span>
        </label>
        <label style="flex: 1; display: flex; align-items: center; cursor: pointer; padding: 1rem; background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem;">
          <input type="radio" name="dry_run" value="1" style="width: 18px; height: 18px; margin-right: 0.75rem;">
          <span>Dry Run</span>
        </label>
      </div>
      <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
        <button type="button" onclick="closeExecuteModal()" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Proceed</button>
      </div>
    </form>
  </div>
</div>

<script src="{{ asset('js/retention.js') }}"></script>
<script>
function openCreatePolicyModal() {
  document.getElementById('modalTitle').textContent = 'Create Retention Policy';
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
  fetch('{{ route("admin.retention.edit", "") }}/' + policyId)
    .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
    .then(data => {
      document.getElementById('modalTitle').textContent = 'Edit Retention Policy';
      document.getElementById('policyForm').action = '{{ route("admin.retention.update", "") }}/' + policyId;
      document.getElementById('methodField').value = 'PUT';
      document.getElementById('policyId').value = policyId;
      document.getElementById('policyName').value = data.policy_name;
      document.getElementById('dataType').value = data.data_type;
      document.getElementById('retentionMonths').value = data.retention_months;
      document.getElementById('backupBeforeDeletion').checked = data.backup_before_deletion;
      document.getElementById('description').value = data.description || '';
      document.getElementById('policyModal').style.display = 'flex';
    })
    .catch(e => alert('Failed to load policy'));
}

function closePolicyModal() {
  document.getElementById('policyModal').style.display = 'none';
}

function executeCleanup(policyId) {
  document.getElementById('executeForm').action = '{{ route("admin.retention.execute", "") }}/' + policyId;
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
</script>
@endsection
