<div id="signatureModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>{{ __('messages.create_e_signature') }}</h3>
            <button type="button" class="modal-close" onclick="closeSignatureModal()">&times;</button>
        </div>
        
        <form id="signatureForm" onsubmit="submitSignature(event)">
            @csrf
            
            <!-- Updated to use flexible record types -->
            <div class="form-group">
                <label for="recordType">{{ __('messages.record_type') }} *</label>
                <select id="recordType" name="record_type" class="form-control" required>
                    <option value="">{{ __('messages.select_record_type') }}</option>
                    @foreach($availableRecordTypes ?? [] as $type)
                        <option value="{{ $type->record_type_key }}">{{ $type->display_name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('messages.select_type_record_to_sign') }}</small>
            </div>

            <div class="form-group">
                <label for="recordId">{{ __('messages.record_id') }} *</label>
                <input type="number" id="recordId" name="record_id" class="form-control" 
                       placeholder="{{ __('messages.enter_id_of_record') }}" required>
                <small class="text-muted">{{ __('messages.unique_identifier_record_to_sign') }}</small>
            </div>

            <div class="form-group">
                <label for="action">{{ __('messages.action') }} *</label>
                <input type="text" id="action" name="action" class="form-control" required readonly>
            </div>

            <div class="form-group">
                <label for="meaningOfSignature">{{ __('messages.meaning_of_signature') }} *</label>
                <textarea id="meaningOfSignature" name="meaning_of_signature" class="form-control" 
                          placeholder="{{ __('messages.meaning_signature_placeholder') }}" 
                          maxlength="500" required></textarea>
                <small class="text-muted">{{ __('messages.explain_intent_meaning_signature') }}</small>
            </div>

            <div class="form-group">
                <label for="reason">{{ __('messages.reason_optional') }}</label>
                <textarea id="reason" name="reason" class="form-control" 
                          placeholder="{{ __('messages.additional_context_notes') }}" maxlength="500"></textarea>
            </div>

            <!-- 2FA Section -->
            <div id="twoFASection" style="display: none;">
                <div class="alert alert-info">
                    <strong>{{ __('messages.two_factor_authentication_required') }}</strong>
                    <p>{{ __('messages.account_has_2fa_enabled') }}</p>
                </div>

                <div class="form-group">
                    <label for="twoFAMethod">{{ __('messages.2fa_method') }} *</label>
                    <select id="twoFAMethod" name="two_fa_method" class="form-control">
                        <option value="">{{ __('messages.select_method') }}</option>
                        <option value="totp">{{ __('messages.authenticator_app_totp') }}</option>
                        <option value="backup_code">{{ __('messages.backup_code') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="twoFACode">{{ __('messages.authentication_code') }} *</label>
                    <input type="text" id="twoFACode" name="two_fa_code" class="form-control" 
                           placeholder="{{ __('messages.6_digit_code_or_backup') }}" maxlength="20">
                </div>
            </div>

            <div class="form-group">
                <label for="password">{{ __('messages.password') }} *</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="{{ __('messages.enter_password_to_confirm') }}" required>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" id="confirmSignature" required>
                    <label for="confirmSignature">
                        {{ __('messages.confirm_authorize_action_legal_implications') }}
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeSignatureModal()">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary" id="signBtn">{{ __('messages.sign') }}</button>
            </div>
        </form>

        <div id="signatureStatus" style="display: none; margin-top: 1rem;">
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
    background-color: white;
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
    border-bottom: 1px solid #e5e7eb;
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
    color: #6b7280;
}

.modal-close:hover {
    color: #000;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
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
    font-size: 0.9rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-info {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    color: #1e40af;
}

.alert-success {
    background-color: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
}

.alert-error {
    background-color: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-primary:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

small.text-muted {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.875rem;
}
</style>

<script>
function openSignatureModal(recordType, recordId, action) {
    if (recordType && recordId) {
        document.getElementById('recordType').value = recordType;
        document.getElementById('recordId').value = recordId;
    }
    document.getElementById('action').value = action || 'sign';
    
    // Check if user has 2FA enabled
    const has2FA = {{ auth()->user()->two_fa_enabled ? 'true' : 'false' }};
    document.getElementById('twoFASection').style.display = has2FA ? 'block' : 'none';
    
    document.getElementById('signatureModal').style.display = 'flex';
}

function closeSignatureModal() {
    document.getElementById('signatureModal').style.display = 'none';
    document.getElementById('signatureForm').reset();
    document.getElementById('signatureStatus').style.display = 'none';
}

async function submitSignature(event) {
    event.preventDefault();
    
    const signBtn = document.getElementById('signBtn');
    signBtn.disabled = true;
    signBtn.textContent = '{{ __("messages.signing") }}...';
    
    const formData = new FormData(document.getElementById('signatureForm'));
    
    try {
        const response = await fetch('{{ route("admin.e-signatures.sign") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSignatureStatus('success', '{{ __("messages.e_signature_created_successfully") }}');
            setTimeout(() => {
                closeSignatureModal();
                location.reload();
            }, 2000);
        } else {
            showSignatureStatus('error', data.message || '{{ __("messages.failed_to_create_signature") }}');
        }
    } catch (error) {
        showSignatureStatus('error', '{{ __("messages.an_error_occurred") }}: ' + error.message);
    } finally {
        signBtn.disabled = false;
        signBtn.textContent = '{{ __("messages.sign") }}';
    }
}

function showSignatureStatus(type, message) {
    const statusDiv = document.getElementById('signatureStatus');
    const messageDiv = document.getElementById('statusMessage');
    
    messageDiv.className = 'alert alert-' + type;
    messageDiv.textContent = message;
    statusDiv.style.display = 'block';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('signatureModal');
    if (event.target === modal) {
        closeSignatureModal();
    }
}
</script>
