<!-- Bulk Actions Toolbar -->
<div id="bulkActionsToolbar" style="display: none; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; position: sticky; top: 0; z-index: 10;">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div>
            <span id="selectedCount" style="color: var(--text-secondary); font-weight: 500;">0 {{ __('messages.documents') }}</span>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <!-- Added i18n for button labels -->
            <button type="button" onclick="bulkApprove()" class="btn btn-primary" style="font-size: 0.875rem;">
                {{ __('messages.bulk_approve') }}
            </button>
            <button type="button" onclick="bulkArchive()" class="btn btn-secondary" style="font-size: 0.875rem;">
                {{ __('messages.bulk_archive') }}
            </button>
            <button type="button" onclick="bulkExport()" class="btn btn-secondary" style="font-size: 0.875rem;">
                {{ __('messages.bulk_export') }}
            </button>
            <button type="button" onclick="clearSelection()" class="btn btn-secondary" style="font-size: 0.875rem;">
                {{ __('messages.deselect_all') }}
            </button>
        </div>
    </div>
</div>

<script>
let selectedDocuments = [];

function toggleDocumentSelection(docId) {
    const checkbox = document.getElementById(`doc-checkbox-${docId}`);
    if (checkbox.checked) {
        if (!selectedDocuments.includes(docId)) {
            selectedDocuments.push(docId);
        }
    } else {
        selectedDocuments = selectedDocuments.filter(id => id !== docId);
    }
    updateBulkActionsToolbar();
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const checkboxes = document.querySelectorAll('[id^="doc-checkbox-"]');
    
    selectedDocuments = [];
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        if (selectAllCheckbox.checked) {
            const docId = checkbox.id.replace('doc-checkbox-', '');
            selectedDocuments.push(parseInt(docId));
        }
    });
    updateBulkActionsToolbar();
}

function updateBulkActionsToolbar() {
    const toolbar = document.getElementById('bulkActionsToolbar');
    const count = document.getElementById('selectedCount');
    
    if (selectedDocuments.length > 0) {
        toolbar.style.display = 'flex';
        count.textContent = `${selectedDocuments.length} document${selectedDocuments.length !== 1 ? 's' : ''} selected`;
    } else {
        toolbar.style.display = 'none';
        selectedDocuments = [];
    }
}

function clearSelection() {
    selectedDocuments = [];
    document.querySelectorAll('[id^="doc-checkbox-"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('select-all-checkbox').checked = false;
    updateBulkActionsToolbar();
}

function bulkApprove() {
    if (selectedDocuments.length === 0) return;
    if (!confirm(`Approve ${selectedDocuments.length} document(s)?`)) return;
    
    // Send bulk approve request
    fetch('{{ route("documents.bulk-approve") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ document_ids: selectedDocuments })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function bulkArchive() {
    if (selectedDocuments.length === 0) return;
    if (!confirm(`Archive ${selectedDocuments.length} document(s)?`)) return;
    
    fetch('{{ route("documents.bulk-archive") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ document_ids: selectedDocuments })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function bulkExport() {
    if (selectedDocuments.length === 0) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("documents.bulk-export") }}';
    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="document_ids" value="${selectedDocuments.join(',')}">
    `;
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
