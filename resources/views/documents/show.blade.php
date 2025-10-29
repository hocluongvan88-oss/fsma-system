@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div style="padding: 2rem 0;">
    <!-- Back Link -->
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('documents.index') }}" style="color: var(--accent-primary); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← {{ __('messages.back_to_documents') }}
        </a>
    </div>

    <!-- Header with Status -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">{{ $document->title }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">{{ $document->doc_number }}</p>
        </div>
        <div>
            @php
                $statusColors = [
                    'draft' => ['bg' => 'rgba(160, 160, 160, 0.1)', 'text' => 'var(--text-secondary)'],
                    'review' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => 'var(--warning)'],
                    'approved' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => 'var(--success)'],
                    'archived' => ['bg' => 'rgba(107, 114, 128, 0.1)', 'text' => 'var(--text-muted)']
                ];
                $colors = $statusColors[$document->status] ?? $statusColors['draft'];
            @endphp
            <span style="display: inline-block; background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 600;">
                {{ __('messages.status_' . $document->status) }}
            </span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; margin-bottom: 2rem;">
        <!-- Left Column -->
        <div>
            <!-- Document Details Card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.document_details') }}</h3>
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary); width: 150px;">{{ __('messages.doc_number') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ $document->doc_number }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.type') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ str_replace('_', ' ', ucwords($document->type)) }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.description') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ $document->description ?? '-' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.version') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ $document->version }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.effective_date') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ $document->effective_date?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.review_date') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">
                                {{ $document->review_date?->format('Y-m-d') ?? '-' }}
                                @if($document->needsReview())
                                <span style="display: inline-block; background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; margin-left: 0.5rem; font-weight: 600;">{{ __('messages.needs_review') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.expiry_date') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">
                                @if($document->expiry_date)
                                    <span style="color: {{ $document->isExpired() ? 'var(--error)' : 'var(--text-primary)' }}; font-weight: {{ $document->isExpired() ? '600' : '400' }};">
                                        {{ $document->expiry_date->format('Y-m-d') }}
                                    </span>
                                    @if($document->isExpired())
                                    <span style="display: inline-block; background: rgba(239, 68, 68, 0.1); color: var(--error); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; margin-left: 0.5rem; font-weight: 600;">{{ __('messages.expired') }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0.75rem 0; font-weight: 600; color: var(--text-secondary);">{{ __('messages.uploaded_by') }}:</td>
                            <td style="padding: 0.75rem 0; color: var(--text-primary);">{{ $document->uploader->name }} {{ __('messages.on') }} {{ $document->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    </table>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                            {{ __('messages.download') }}
                        </a>
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-secondary">
                            {{ __('messages.edit') }}
                        </a>
                        @if($document->canBeApproved() && (auth()->user()->isAdmin() || auth()->user()->role === 'admin'))
                        <form method="POST" action="{{ route('documents.approve', $document) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.approve') }}
                            </button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-secondary" onclick="openNewVersionModal()">
                            {{ __('messages.new_version') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Added approval workflow section -->
            @include('documents.partials.approval-workflow')

            <!-- Added metadata display section -->
            @include('documents.partials.metadata-display')

            <!-- Added file integrity section -->
            @include('documents.partials.file-integrity')

            <!-- Added version timeline section -->
            @include('documents.partials.version-timeline')

            <!-- Added audit trail section -->
            @include('documents.partials.audit-trail')
        </div>

        <!-- Right Sidebar -->
        <div>
            <!-- Quick Info Card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.quick_info') }}</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; margin-bottom: 0.25rem;">{{ __('messages.file_name') }}</div>
                            <div style="color: var(--text-primary); word-break: break-word; font-size: 0.875rem;">{{ $document->file_name }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; margin-bottom: 0.25rem;">{{ __('messages.file_size') }}</div>
                            <div style="color: var(--text-primary);">{{ $document->file_size_human }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; margin-bottom: 0.25rem;">{{ __('messages.file_type') }}</div>
                            <div style="color: var(--text-primary);">{{ $document->file_type }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Version Modal -->
<div id="newVersionModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <form method="POST" action="{{ route('documents.new-version', $document) }}" enctype="multipart/form-data">
            @csrf
            
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin: 0;">{{ __('messages.upload_new_version') }}</h3>
                <button type="button" onclick="closeNewVersionModal()" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 1.5rem; padding: 0;">×</button>
            </div>

            <div style="padding: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.new_file') }} *</label>
                    <input type="file" class="form-input" name="file" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.change_type') }} *</label>
                    <select name="change_type" class="form-select" required>
                        <option value="">{{ __('messages.select_change_type') }}</option>
                        <option value="major">{{ __('messages.major_breaking_changes') }}</option>
                        <option value="minor">{{ __('messages.minor_new_features') }}</option>
                        <option value="patch">{{ __('messages.patch_bug_fixes') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.change_notes') }} *</label>
                    <textarea class="form-textarea" name="change_notes" rows="3" required placeholder="{{ __('messages.describe_changes') }}"></textarea>
                </div>
            </div>

            <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" onclick="closeNewVersionModal()" class="btn btn-secondary">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('messages.upload_new_version') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewVersionModal() {
    document.getElementById('newVersionModal').style.display = 'flex';
}

function closeNewVersionModal() {
    document.getElementById('newVersionModal').style.display = 'none';
}

document.getElementById('newVersionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNewVersionModal();
    }
});
</script>

<style>
    @media (max-width: 768px) {
        [style*="grid-template-columns: 1fr 350px"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection
