@extends('layouts.app')

@section('title', __('messages.notifications'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 700;">{{ __('messages.all_notifications') }}</h2>
    
    @if($notifications->where('is_read', false)->count() > 0)
    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-secondary">{{ __('messages.mark_all_as_read') }}</button>
    </form>
    @endif
</div>

<div class="card">
    @forelse($notifications as $notification)
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); {{ $notification->is_read ? 'opacity: 0.6;' : '' }}">
        <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    @if(!$notification->is_read)
                    <span style="width: 8px; height: 8px; background: var(--accent-primary); border-radius: 50%;"></span>
                    @endif
                    <h3 style="font-size: 1.125rem; font-weight: 600;">{{ $notification->title }}</h3>
                    <span class="badge badge-{{ $notification->type === 'quota_warning' ? 'warning' : ($notification->type === 'quota_reached' ? 'error' : 'success') }}">
                        {{ __('messages.notification_type_' . str_replace('_', '.', $notification->type)) }}
                    </span>
                </div>
                
                <p style="color: var(--text-secondary); margin-bottom: 0.75rem;">{{ $notification->message }}</p>
                
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                    
                    @if($notification->cta_url)
                    <a href="{{ $notification->cta_url }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        {{ $notification->cta_text }}
                    </a>
                    @endif
                    
                    @if(!$notification->is_read)
                    <button onclick="markAsRead({{ $notification->id }})" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                        {{ __('messages.mark_as_read') }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🔔</div>
        <p>{{ __('messages.no_notifications') }}</p>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div class="pagination">
    {{ $notifications->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
function markAsRead(id) {
    fetch(`/notifications/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush
