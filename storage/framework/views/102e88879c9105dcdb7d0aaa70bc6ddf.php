<div style="position: relative;" x-data="{ open: false, count: 0 }" x-init="
    // Fetch unread count on load
    fetch('/notifications/unread-count')
        .then(r => r.json())
        .then(data => count = data.count);
    
    // Poll every 30 seconds
    setInterval(() => {
        fetch('/notifications/unread-count')
            .then(r => r.json())
            .then(data => count = data.count);
    }, 30000);
">
    <button @click="open = !open" class="btn btn-secondary" style="position: relative; padding: 0.75rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        
        <span x-show="count > 0" x-text="count" style="position: absolute; top: -4px; right: -4px; background: var(--error); color: white; font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.375rem; border-radius: 9999px; min-width: 18px; text-align: center;"></span>
    </button>
    
    <div x-show="open" @click.away="open = false" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; width: 360px; max-width: 90vw; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.3); z-index: 1000; max-height: 400px; overflow-y: auto;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-weight: 600;"><?php echo e(__('messages.notifications')); ?></h3>
            <a href="<?php echo e(route('notifications.index')); ?>" style="font-size: 0.875rem; color: var(--accent-primary); text-decoration: none;"><?php echo e(__('messages.view_all')); ?></a>
        </div>
        
        <div id="notification-list">
            <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                <?php echo e(__('messages.loading')); ?>

            </div>
        </div>
    </div>
</div>

<script>
// Fetch recent notifications when bell is clicked
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        open: false,
        count: 0,
        init() {
            this.fetchCount();
            setInterval(() => this.fetchCount(), 30000);
        },
        fetchCount() {
            fetch('/notifications/unread-count')
                .then(r => r.json())
                .then(data => this.count = data.count);
        }
    }));
});
</script>
<?php /**PATH /home/fxiasdcg/root/resources/views/components/notification-bell.blade.php ENDPATH**/ ?>