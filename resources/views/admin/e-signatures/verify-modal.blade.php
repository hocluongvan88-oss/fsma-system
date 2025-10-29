<div id="verifyModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3>Verify E-Signature</h3>
            <button type="button" class="modal-close" onclick="closeVerifyModal()">&times;</button>
        </div>
        
        <form id="verifyForm" onsubmit="submitVerify(event)">
            @csrf
            
            <input type="hidden" id="verifySignatureId" name="signature_id">
            
            <div class="signature-details">
                <div class="detail-row">
                    <span class="detail-label">Signed By:</span>
                    <span class="detail-value" id="verifyUser">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Signed At:</span>
                    <span class="detail-value" id="verifyTime">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Record Type:</span>
                    <span class="detail-value" id="verifyRecordType">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Record ID:</span>
                    <span class="detail-value" id="verifyRecordId">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Action:</span>
                    <span class="detail-value" id="verifyAction">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Meaning of Signature:</span>
                    <span class="detail-value" id="verifyMeaning">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">IP Address:</span>
                    <span class="detail-value" id="verifyIP">-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Algorithm:</span>
                    <span class="detail-value" id="verifyAlgorithm">-</span>
                </div>
            </div>

            <div class="form-group">
                <label for="verifyPassword">Enter Your Password to Verify *</label>
                <input type="password" id="verifyPassword" name="password" class="form-control" 
                       placeholder="Your password" required>
                <small class="text-muted">Password is required to verify the signature integrity</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeVerifyModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="verifyBtn">Verify Signature</button>
            </div>
        </form>

        <div id="verifyStatus" style="display: none; margin-top: 1rem;">
            <div id="verifyMessage" class="alert"></div>
        </div>
    </div>
</div>

<style>
.signature-details {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #374151;
    min-width: 150px;
}

.detail-value {
    color: #6b7280;
    word-break: break-word;
    flex: 1;
    text-align: right;
}
</style>

<script>
function openVerifyModal(signatureId, user, time, recordType, recordId, action, meaning, ip, algorithm) {
    document.getElementById('verifySignatureId').value = signatureId;
    document.getElementById('verifyUser').textContent = user;
    document.getElementById('verifyTime').textContent = time;
    document.getElementById('verifyRecordType').textContent = recordType;
    document.getElementById('verifyRecordId').textContent = recordId;
    document.getElementById('verifyAction').textContent = action;
    document.getElementById('verifyMeaning').textContent = meaning || '-';
    document.getElementById('verifyIP').textContent = ip;
    document.getElementById('verifyAlgorithm').textContent = algorithm;
    
    document.getElementById('verifyModal').style.display = 'flex';
}

function closeVerifyModal() {
    document.getElementById('verifyModal').style.display = 'none';
    document.getElementById('verifyForm').reset();
    document.getElementById('verifyStatus').style.display = 'none';
}

async function submitVerify(event) {
    event.preventDefault();
    
    const verifyBtn = document.getElementById('verifyBtn');
    verifyBtn.disabled = true;
    verifyBtn.textContent = 'Verifying...';
    
    const formData = new FormData(document.getElementById('verifyForm'));
    
    try {
        const response = await fetch('{{ route("admin.e-signatures.verify") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            const statusClass = data.valid ? 'alert-success' : 'alert-error';
            const message = data.valid ? 
                'Signature is VALID - Record content has not been modified' : 
                'Signature is INVALID - Record content may have been modified';
            showVerifyStatus(statusClass, message);
        } else {
            showVerifyStatus('alert-error', data.message || 'Verification failed');
        }
    } catch (error) {
        showVerifyStatus('alert-error', 'An error occurred: ' + error.message);
    } finally {
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify Signature';
    }
}

function showVerifyStatus(type, message) {
    const statusDiv = document.getElementById('verifyStatus');
    const messageDiv = document.getElementById('verifyMessage');
    
    messageDiv.className = 'alert ' + type;
    messageDiv.textContent = message;
    statusDiv.style.display = 'block';
}

window.onclick = function(event) {
    const modal = document.getElementById('verifyModal');
    if (event.target === modal) {
        closeVerifyModal();
    }
}
</script>
