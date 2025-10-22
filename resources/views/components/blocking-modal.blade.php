<div id="blocking-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 1rem; max-width: 500px; width: 90%; padding: 2rem; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">�7�2�1�5</div>
        <h2 id="modal-title" style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);"></h2>
        <p id="modal-message" style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.6;"></p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <a id="modal-cta" href="#" class="btn btn-primary" style="display: none;"></a>
            <button onclick="closeBlockingModal()" class="btn btn-secondary">�0�3��ng</button>
        </div>
    </div>
</div>

<script>
function checkBlockingNotification() {
    console.log('[v0] Checking for blocking notifications...');
    
    fetch('/notifications/blocking')
        .then(r => {
            console.log('[v0] Response status:', r.status);
            return r.json();
        })
        .then(notification => {
            console.log('[v0] Notification data:', notification);
            
            if (notification && notification.id) {
                // Set title with fallback
                const title = notification.title || 'Th�0�0ng b��o quan tr�6�9ng';
                const message = notification.message || 'Vui l��ng li��n h�6�3 qu�5�7n tr�6�7 vi��n �0�4�6�9 bi�6�5t th��m chi ti�6�5t.';
                const ctaText = notification.cta_text || 'Xem chi ti�6�5t';
                const ctaUrl = notification.cta_url || '/dashboard';
                
                console.log('[v0] Displaying modal with:', { title, message, ctaText, ctaUrl });
                
                document.getElementById('modal-title').textContent = title;
                document.getElementById('modal-message').textContent = message;
                
                const ctaButton = document.getElementById('modal-cta');
                ctaButton.textContent = ctaText;
                ctaButton.href = ctaUrl;
                ctaButton.style.display = 'inline-block';
                
                document.getElementById('blocking-modal').style.display = 'flex';
            } else {
                console.log('[v0] No blocking notification found or invalid data');
            }
        })
        .catch(error => {
            console.error('[v0] Error fetching blocking notification:', error);
        });
}

function closeBlockingModal() {
    console.log('[v0] Closing blocking modal');
    document.getElementById('blocking-modal').style.display = 'none';
}

// Check on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] DOM loaded, checking for blocking notifications');
    checkBlockingNotification();
});
</script>
