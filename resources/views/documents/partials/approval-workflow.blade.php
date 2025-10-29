<!-- Approval Workflow Section -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <!-- Added i18n for title -->
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.approval_workflow') }}</h3>
        
        @if($document->status === 'approved' && $document->approved_by)
        <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid var(--success); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="font-size: 1.25rem;">✓</span>
                <strong style="color: var(--success);">{{ __('messages.approved') }}</strong>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                {{ __('messages.by') }} {{ $document->approver->name }} {{ __('messages.on') }} {{ $document->approved_at->format('Y-m-d H:i') }}
            </div>
        </div>
        @elseif($document->status === 'review')
        <div style="background: rgba(245, 158, 11, 0.1); border-left: 4px solid var(--warning); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="font-size: 1.25rem;">⏳</span>
                <strong style="color: var(--warning);">{{ __('messages.review') }}</strong>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                {{ __('messages.needs_review') }}
            </div>
        </div>
        @elseif($document->status === 'draft')
        <div style="background: rgba(160, 160, 160, 0.1); border-left: 4px solid var(--text-secondary); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="font-size: 1.25rem;">📝</span>
                <strong style="color: var(--text-secondary);">{{ __('messages.draft') }}</strong>
            </div>
            <div style="color: var(--text-secondary); font-size: 0.875rem;">
                {{ __('messages.draft') }}
            </div>
        </div>
        @endif

        <!-- Approval Timeline -->
        @if($document->approvals->count() > 0)
        <div style="margin-top: 1.5rem;">
            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.approval_history') }}</h4>
            <div style="border-left: 2px solid var(--border-color); padding-left: 1rem;">
                @foreach($document->approvals as $approval)
                <div style="margin-bottom: 1rem; position: relative;">
                    <div style="position: absolute; left: -1.5rem; top: 0; width: 12px; height: 12px; background: var(--accent-primary); border-radius: 50%; border: 2px solid var(--bg-secondary);"></div>
                    <div style="font-weight: 600; color: var(--text-primary);">{{ $approval->approver->name }}</div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ $approval->created_at->format('Y-m-d H:i') }}</div>
                    @if($approval->notes)
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem; font-style: italic;">{{ $approval->notes }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Approve Button -->
        @if($document->canBeApproved() && (auth()->user()->isAdmin() || auth()->user()->role === 'admin'))
        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <form method="POST" action="{{ route('documents.approve', $document) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    {{ __('messages.approve') }}
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
