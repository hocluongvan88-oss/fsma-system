<div id="revokeModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>{{ __('messages.revoke_e_signature') }}</h3>
            <button type="button" class="modal-close" onclick="closeRevokeModal()">&times;</button>
        </div>
        
        <form id="revokeForm" onsubmit="submitRevoke(event)">
            @csrf
            
            <input type="hidden" id="revokeSignatureId" name="signature_id">
            
            <div class="alert alert-warning">
                <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.revoke_signature_warning') }}
            </div>
            
            <div class="signature-preview">
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.signed_by') }}:</span>
                    <span class="preview-value" id="revokeUser">-</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.signed_at') }}:</span>
                    <span class="preview-value" id="revokeTime">-</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.record') }}:</span>
                    <span class="preview-value" id="revokeRecord">-</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.action') }}:</span>
                    <span class="preview-value" id="revokeAction">-</span>
                </div>
            </div>

            <div class="form-group">
                <label for="revocationReason">{{ __('messages.revocation_reason') }} *</label>
                <select id="revocationReason" name="revocation_reason_type" class="form-control" required onchange="updateReasonField()">
                    <option value="">{{ __('messages.select_a_reason') }}</option>
                    <option value="unauthorized">{{ __('messages.unauthorized_action') }}</option>
                    <option value="error">{{ __('messages.data_entry_error') }}</option>
                    <option value="correction">{{ __('messages.correction_required') }}</option>
                    <option value="fraud">{{ __('messages.suspected_fraud') }}</option>
                    <option value="compliance">{{ __('messages.compliance_issue') }}</option>
                    <option value="other">{{ __('messages.other') }}</option>
                </select>
            </div>

            <div class="form-group">
                <label for="revocationDetails">{{ __('messages.additional_details') }} *</label>
                <textarea id="revocationDetails" name="reason" class="form-control" 
                          placeholder="{{ __('messages.provide_detailed_explanation') }}" 
                          maxlength="500" required></textarea>
                <small class="text-muted">{{ __('messages.recorded_in_audit_trail') }}</small>
            </div>

            <div class="form-group">
                <label for="revokePassword">{{ __('messages.your_password') }} *</label>
                <input type="password" id="revokePassword" name="password" class="form-control" 
                       placeholder="{{ __('messages.confirm_with_password') }}" required>
                <small class="text-muted">{{ __('messages.required_to_authorize_revocation') }}</small>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" id="confirmRevoke" required>
                    <label for="confirmRevoke">
                        {{ __('messages.confirm_revoke_signature') }}
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRevokeModal()">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-danger" id="revokeBtn">{{ __('messages.revoke_signature') }}</button>
            </div>
        </form>

        <div id="revokeStatus" style="display: none; margin-top: 1rem;">
            <div id="revokeMessage" class="alert"></div>
        </div>
    </div>
</div>

<style>
.alert-warning {
    background-color: #fef3c7;
    border: 1px solid #fcd34d;
    color: #92400e;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.signature-preview {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.preview-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.preview-row:last-child {
    border-bottom: none;
}

.preview-label {
    font-weight: 600;
    color: #374151;
    min-width: 120px;
}

.preview-value {
    color: #6b7280;
    text-align: right;
    flex: 1;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-danger:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}
</style>

<script>
function openRevokeModal(signatureId, user, time, recordType, recordId, action) {
    document.getElementById('revokeSignatureId').value = signatureId;
    document.getElementById('revokeUser').textContent = user;
    document.getElementById('revokeTime').textContent = time;
    document.getElementById('revokeRecord').textContent = recordType + ' #' + recordId;
    document.getElementById('revokeAction').textContent = action;
    
    document.getElementById('revokeModal').style.display = 'flex';
}

function closeRevokeModal() {
    document.getElementById('revokeModal').style.display = 'none';
    document.getElementById('revokeForm').reset();
    document.getElementById('revokeStatus').style.display = 'none';
}

function updateReasonField() {
    const reasonType = document.getElementById('revocationReason').value;
    const detailsField = document.getElementById('revocationDetails');
    
    const templates = {
        'unauthorized': '{{ __("messages.unauthorized_signature_template") }}',
        'error': '{{ __("messages.error_signature_template") }}',
        'correction': '{{ __("messages.correction_signature_template") }}',
        'fraud': '{{ __("messages.fraud_signature_template") }}',
        'compliance': '{{ __("messages.compliance_signature_template") }}',
        'other': ''
    };
    
    detailsField.placeholder = templates[reasonType] || '{{ __("messages.provide_detailed_explanation") }}';
}

async function submitRevoke(event) {
    event.preventDefault();
    
    const revokeBtn = document.getElementById('revokeBtn');
    revokeBtn.disabled = true;
    revokeBtn.textContent = '{{ __("messages.revoking") }}...';
    
    const formData = new FormData(document.getElementById('revokeForm'));
    
    try {
        const response = await fetch('{{ route("admin.e-signatures.revoke") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showRevokeStatus('alert-success', '{{ __("messages.signature_revoked_successfully") }}');
            setTimeout(() => {
                closeRevokeModal();
                location.reload();
            }, 2000);
        } else {
            showRevokeStatus('alert-error', data.message || '{{ __("messages.failed_to_revoke_signature") }}');
        }
    } catch (error) {
        showRevokeStatus('alert-error', '{{ __("messages.an_error_occurred") }}: ' + error.message);
    } finally {
        revokeBtn.disabled = false;
        revokeBtn.textContent = '{{ __("messages.revoke_signature") }}';
    }
}

function showRevokeStatus(type, message) {
    const statusDiv = document.getElementById('revokeStatus');
    const messageDiv = document.getElementById('revokeMessage');
    
    messageDiv.className = 'alert ' + type;
    messageDiv.textContent = message;
    statusDiv.style.display = 'block';
}

window.onclick = function(event) {
    const modal = document.getElementById('revokeModal');
    if (event.target === modal) {
        closeRevokeModal();
    }
}
</script>
