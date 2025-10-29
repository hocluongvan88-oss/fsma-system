<div style="position: relative;" x-data="notificationBell()" x-init="init()">
    <!-- Simplified Alpine.js initialization - removed duplicate code -->
    <button @click="open = !open" class="btn btn-secondary" style="position: relative; padding: 0.75rem;" aria-label="Thông báo">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        
        <span x-show="count > 0" x-text="count" aria-live="polite" style="position: absolute; top: -4px; right: -4px; background: var(--error); color: white; font-size: 0.625rem; font-weight: 700; padding: 0.125rem 0.375rem; border-radius: 9999px; min-width: 18px; text-align: center;"></span>
    </button>
    
    <!-- Moved dropdown to right side (right: 0) and added loading state handling -->
    <div x-show="open" @click.away="open = false" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; width: 360px; max-width: 90vw; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.3); z-index: 1000; max-height: 400px; overflow-y: auto;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-weight: 600;">{{ __('messages.notifications') }}</h3>
            <a href="{{ route('notifications.index') }}" style="font-size: 0.875rem; color: var(--accent-primary); text-decoration: none;">{{ __('messages.view_all') }}</a>
        </div>
        
        <!-- Added loading and error states with proper notification list rendering -->
        <div id="notification-list">
            <div x-show="loading" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid var(--border-color); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 0.6s linear infinite;"></div>
                <p style="margin-top: 0.5rem;">{{ __('messages.loading') }}</p>
            </div>
            
            <div x-show="!loading && notifications.length === 0" style="padding: 2rem; text-align: center; color: var(--text-muted);">
                {{ __('messages.no_notifications') }}
            </div>
            
            <div x-show="!loading && notifications.length > 0">
                <template x-for="notification in notifications" :key="notification.id">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s;" @click="markAsRead(notification.id)" :style="notification.is_read ? '' : 'background: rgba(59, 130, 246, 0.05);'">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 0.75rem;">
                            <div style="flex: 1;">
                                <h4 style="font-weight: 600; margin-bottom: 0.25rem;" x-text="notification.title"></h4>
                                <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;" x-text="notification.message"></p>
                                <span style="font-size: 0.75rem; color: var(--text-muted);" x-text="formatTime(notification.created_at)"></span>
                            </div>
                            <span x-show="!notification.is_read" style="width: 8px; height: 8px; background: var(--accent-primary); border-radius: 50%; flex-shrink: 0; margin-top: 0.25rem;"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notificationBell', () => ({
        open: false,
        count: 0,
        notifications: [],
        loading: false,
        
        init() {
            this.fetchCount();
            this.fetchNotifications();
            
            // Poll every 30 seconds
            setInterval(() => {
                this.fetchCount();
                if (this.open) {
                    this.fetchNotifications();
                }
            }, 30000);
            
            // Fetch notifications when dropdown opens
            this.$watch('open', (value) => {
                if (value) {
                    this.fetchNotifications();
                }
            });
        },
        
        fetchCount() {
            fetch('/notifications/unread-count')
                .then(r => r.json())
                .then(data => {
                    this.count = data.count || 0;
                    console.log('[v0] Unread count updated:', this.count);
                })
                .catch(error => {
                    console.error('[v0] Failed to fetch unread count:', error);
                });
        },
        
        fetchNotifications() {
            this.loading = true;
            
            fetch('/notifications/list?limit=5')
                .then(r => {
                    if (!r.ok) throw new Error('Failed to fetch notifications');
                    return r.json();
                })
                .then(data => {
                    this.notifications = data.data || [];
                    this.loading = false;
                    console.log('[v0] Notifications loaded:', this.notifications.length);
                })
                .catch(error => {
                    console.error('[v0] Failed to fetch notifications:', error);
                    this.loading = false;
                    this.notifications = [];
                });
        },
        
        markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-read`, { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    // Update notification in list
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                    }
                    this.fetchCount();
                    console.log('[v0] Notification marked as read:', notificationId);
                })
                .catch(error => console.error('[v0] Failed to mark notification as read:', error));
        },
        
        formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Vừa xong';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' phút trước';
            if (seconds < 86400) return Math.floor(seconds / 3600) + ' giờ trước';
            return Math.floor(seconds / 86400) + ' ngày trước';
        }
    }));
});
</script>
