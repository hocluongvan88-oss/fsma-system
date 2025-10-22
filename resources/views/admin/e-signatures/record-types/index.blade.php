@extends('layouts.app')

@section('title', __('messages.signature_record_types'))

@section('content')
<div class="card">
    <!-- Translated header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ __('messages.signature_record_types') }}</h2>
        <button class="btn btn-primary" onclick="openRecordTypeModal()">
            + {{ __('messages.register_new_type') }}
        </button>
    </div>

    <!-- Translated alert -->
    <div class="alert alert-info" style="margin-bottom: 1.5rem;">
        <strong>{{ __('messages.flexible_record_types') }}:</strong> {{ __('messages.register_any_model_signable') }}
    </div>

    <!-- Translated search and filter -->
    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" id="searchInput" placeholder="{{ __('messages.search_by_record_type') }}" 
               class="form-control" style="flex: 1; min-width: 250px;">
        <select id="filterStatus" class="form-control" style="min-width: 150px;">
            <option value="">{{ __('messages.all_status') }}</option>
            <option value="active">{{ __('messages.active_only') }}</option>
            <option value="inactive">{{ __('messages.inactive_only') }}</option>
        </select>
    </div>

    <!-- Translated table -->
    <div class="table-container" style="overflow-x: auto; margin-bottom: 1rem;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.id') }}</th>
                    <th>{{ __('messages.record_type') }}</th>
                    <th>{{ __('messages.model_class') }}</th>
                    <th>{{ __('messages.display_name') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.signatures_count') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody id="recordTypesTable">
                @forelse($recordTypes as $type)
                <tr class="record-type-row" data-record-type="{{ $type->record_type }}" 
                    data-model="{{ $type->model_class }}" data-display="{{ $type->display_name }}"
                    data-status="{{ $type->is_active ? 'active' : 'inactive' }}">
                    <td>{{ $type->id }}</td>
                    <td><code style="font-size: 0.75rem;">{{ $type->record_type }}</code></td>
                    <td><code style="font-size: 0.75rem;">{{ $type->model_class }}</code></td>
                    <td>{{ $type->display_name }}</td>
                    <td>{{ $type->description ?? '-' }}</td>
                    <td>
                        @if($type->is_active)
                            <span class="badge badge-success">{{ __('messages.active') }}</span>
                        @else
                            <span class="badge badge-secondary">{{ __('messages.inactive') }}</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $type->signatures_count ?? 0 }}</td>
                    <td>
                        <button onclick="editRecordType({{ $type->id }})" class="btn btn-sm" title="{{ __('messages.edit') }}">{{ __('messages.edit') }}</button>
                        @if($type->is_active)
                            <button onclick="toggleRecordType({{ $type->id }}, false)" class="btn btn-sm btn-secondary" title="{{ __('messages.deactivate') }}">{{ __('messages.deactivate') }}</button>
                        @else
                            <button onclick="toggleRecordType({{ $type->id }}, true)" class="btn btn-sm btn-primary" title="{{ __('messages.activate') }}">{{ __('messages.activate') }}</button>
                        @endif
                        <button onclick="deleteRecordType({{ $type->id }})" class="btn btn-sm btn-danger" title="{{ __('messages.delete') }}">{{ __('messages.delete') }}</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                        {{ __('messages.no_record_types_registered') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Record Type Modal -->
<div id="recordTypeModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="modalTitle">{{ __('messages.register_new_record_type') }}</h3>
            <button type="button" class="modal-close" onclick="closeRecordTypeModal()">&times;</button>
        </div>
        
        <form id="recordTypeForm" onsubmit="submitRecordType(event)">
            @csrf
            <input type="hidden" id="recordTypeId" name="id">
            
            <div class="form-group">
                <label for="recordTypeKey">{{ __('messages.record_type_key') }} *</label>
                <input type="text" id="recordTypeKey" name="record_type_key" class="form-control" 
                       placeholder="e.g., products, shipments, invoices" required>
                <small class="text-muted">{{ __('messages.unique_identifier_lowercase_no_spaces') }}</small>
            </div>

            <div class="form-group">
                <label for="modelClass">{{ __('messages.model_class') }} *</label>
                <input type="text" id="modelClass" name="model_class" class="form-control" 
                       placeholder="e.g., App\Models\Product" required>
                <small class="text-muted">{{ __('messages.full_namespace_path_model') }}</small>
            </div>

            <div class="form-group">
                <label for="displayName">{{ __('messages.display_name') }} *</label>
                <input type="text" id="displayName" name="display_name" class="form-control" 
                       placeholder="e.g., Products, Shipments, Invoices" required>
                <small class="text-muted">{{ __('messages.human_readable_name_ui') }}</small>
            </div>

            <div class="form-group">
                <label for="description">{{ __('messages.description') }}</label>
                <textarea id="description" name="description" class="form-control" rows="2"
                          placeholder="{{ __('messages.optional_description_record_type') }}"></textarea>
            </div>

            <div class="form-group">
                <label for="contentFields">{{ __('messages.content_fields') }} *</label>
                <textarea id="contentFields" name="content_fields" class="form-control" rows="4"
                          placeholder="id&#10;name&#10;sku&#10;price&#10;updated_at" required></textarea>
                <small class="text-muted">{{ __('messages.one_field_per_line_signature_hash') }}</small>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" id="isActive" name="is_active" checked>
                    <label for="isActive">{{ __('messages.active_allow_signatures') }}</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRecordTypeModal()">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">{{ __('messages.register') }}</button>
            </div>
        </form>

        <div id="recordTypeStatus" style="display: none; margin-top: 1rem;">
            <div id="statusMessage" class="alert"></div>
        </div>
    </div>
</div>

<style>

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: var(--bg-primary);
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1rem;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
}

.modal-close:hover {
    color: var(--text-primary);
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    border-top: 1px solid var(--border-color);
    padding-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-secondary);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    font-size: 1rem;
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.form-control:focus {
    outline: none;
    border-color: var(--accent-primary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
    font-family: monospace;
}

.checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.checkbox input[type="checkbox"] {
    margin-top: 0.25rem;
}

.checkbox label {
    margin: 0;
    font-weight: normal;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-info {
    background-color: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    color: var(--accent-primary);
}

.alert-success {
    background-color: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: var(--success);
}

.alert-error {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: var(--error);
}

small.text-muted {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-success {
    background-color: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.badge-secondary {
    background-color: rgba(107, 114, 128, 0.2);
    color: #6b7280;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.record-type-row.hidden {
    display: none;
}
</style>

<script>
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.record-type-row');
    
    rows.forEach(row => {
        const recordType = row.dataset.recordType.toLowerCase();
        const model = row.dataset.model.toLowerCase();
        const display = row.dataset.display.toLowerCase();
        const status = row.dataset.status;
        
        const matchesSearch = recordType.includes(searchTerm) || 
                            model.includes(searchTerm) || 
                            display.includes(searchTerm);
        
        const matchesStatus = !statusFilter || status === statusFilter;
        
        row.classList.toggle('hidden', !(matchesSearch && matchesStatus));
    });
}

function openRecordTypeModal() {
    document.getElementById('modalTitle').textContent = '{{ __('messages.register_new_record_type') }}';
    document.getElementById('recordTypeForm').reset();
    document.getElementById('recordTypeId').value = '';
    document.getElementById('recordTypeModal').style.display = 'flex';
}

function closeRecordTypeModal() {
    document.getElementById('recordTypeModal').style.display = 'none';
    document.getElementById('recordTypeStatus').style.display = 'none';
}

async function editRecordType(id) {
    try {
        const response = await fetch(`/admin/e-signatures/record-types/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const type = data.record_type;
            document.getElementById('modalTitle').textContent = '{{ __('messages.edit_record_type') }}';
            document.getElementById('recordTypeId').value = type.id;
            document.getElementById('recordTypeKey').value = type.record_type;
            document.getElementById('modelClass').value = type.model_class;
            document.getElementById('displayName').value = type.display_name;
            document.getElementById('description').value = type.description || '';
            document.getElementById('contentFields').value = Array.isArray(type.content_fields) 
                ? type.content_fields.join('\n') 
                : '';
            document.getElementById('isActive').checked = type.is_active;
            
            document.getElementById('recordTypeModal').style.display = 'flex';
        }
    } catch (error) {
        alert('{{ __('messages.error_loading_record_type') }}: ' + error.message);
    }
}

async function submitRecordType(event) {
    event.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = '{{ __('messages.saving') }}...';
    
    const formData = new FormData(document.getElementById('recordTypeForm'));
    const id = document.getElementById('recordTypeId').value;
    
    // Convert content_fields textarea to array
    const contentFields = formData.get('content_fields').split('\n').filter(f => f.trim());
    formData.delete('content_fields');
    
    const data = {
        record_type_key: formData.get('record_type_key'),
        model_class: formData.get('model_class'),
        display_name: formData.get('display_name'),
        description: formData.get('description'),
        content_fields: contentFields,
        is_active: formData.get('is_active') ? true : false
    };
    
    try {
        const url = id ? `/admin/e-signatures/record-types/${id}` : '/admin/e-signatures/record-types';
        const method = id ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('success', '{{ __('messages.record_type_saved_successfully') }}!');
            setTimeout(() => {
                closeRecordTypeModal();
                location.reload();
            }, 1500);
        } else {
            showStatus('error', result.message || '{{ __('messages.failed_to_save_record_type') }}');
        }
    } catch (error) {
        showStatus('error', '{{ __('messages.an_error_occurred') }}: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = id ? '{{ __('messages.update') }}' : '{{ __('messages.register') }}';
    }
}

async function toggleRecordType(id, activate) {
    if (!confirm(`{{ __('messages.are_you_sure_activate_deactivate') }} ${activate ? '{{ __('messages.activate') }}' : '{{ __('messages.deactivate') }}'} {{ __('messages.this_record_type') }}?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/e-signatures/record-types/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ is_active: activate })
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('{{ __('messages.error') }}: ' + data.message);
        }
    } catch (error) {
        alert('{{ __('messages.an_error_occurred') }}: ' + error.message);
    }
}

async function deleteRecordType(id) {
    if (!confirm('{{ __('messages.are_you_sure_delete_record_type') }}')) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/e-signatures/record-types/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('{{ __('messages.error') }}: ' + data.message);
        }
    } catch (error) {
        alert('{{ __('messages.an_error_occurred') }}: ' + error.message);
    }
}

function showStatus(type, message) {
    const statusDiv = document.getElementById('recordTypeStatus');
    const messageDiv = document.getElementById('statusMessage');
    
    messageDiv.className = 'alert alert-' + type;
    messageDiv.textContent = message;
    statusDiv.style.display = 'block';
}

window.onclick = function(event) {
    const modal = document.getElementById('recordTypeModal');
    if (event.target === modal) {
        closeRecordTypeModal();
    }
}
</script>
@endsection
