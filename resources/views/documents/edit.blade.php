@extends('layouts.app')

@section('title', __('messages.edit_document'))

@section('content')
<div style="padding: 2rem 0;">
    <div style="max-width: 800px;">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">{{ __('messages.edit_document') }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2rem;">{{ __('messages.update_document_metadata') }}</p>

        @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--error); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
            <strong style="color: var(--error); display: block; margin-bottom: 0.5rem;">{{ __('messages.please_fix_errors') }}</strong>
            <ul style="margin: 0; padding-left: 1.25rem; color: var(--error); font-size: 0.875rem;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Edit Form -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1.5rem;">
                <form method="POST" action="{{ route('documents.update', $document) }}">
                    @csrf
                    @method('PUT')

                    <!-- Document Number (Read-only) -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.doc_number') }}</label>
                        <input type="text" class="form-input" value="{{ $document->doc_number }}" disabled style="background: var(--bg-tertiary); cursor: not-allowed;">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.25rem;">{{ __('messages.doc_number_cannot_change') }}</small>
                    </div>

                    <!-- Title -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.title') }} *</label>
                        <input type="text" class="form-input" name="title" 
                               value="{{ old('title', $document->title) }}" required>
                        @error('title')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Document Type -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.type') }} *</label>
                        <select class="form-select" name="type" required>
                            <option value="traceability_plan" {{ old('type', $document->type) == 'traceability_plan' ? 'selected' : '' }}>{{ __('messages.traceability_plan') }}</option>
                            <option value="sop" {{ old('type', $document->type) == 'sop' ? 'selected' : '' }}>{{ __('messages.sop') }}</option>
                            <option value="fda_correspondence" {{ old('type', $document->type) == 'fda_correspondence' ? 'selected' : '' }}>{{ __('messages.fda_correspondence') }}</option>
                            <option value="training_material" {{ old('type', $document->type) == 'training_material' ? 'selected' : '' }}>{{ __('messages.training_material') }}</option>
                            <option value="audit_report" {{ old('type', $document->type) == 'audit_report' ? 'selected' : '' }}>{{ __('messages.audit_report') }}</option>
                            <option value="other" {{ old('type', $document->type) == 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                        </select>
                        @error('type')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.description') }}</label>
                        <textarea class="form-textarea" name="description" rows="3">{{ old('description', $document->description) }}</textarea>
                        @error('description')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.effective_date') }}</label>
                            <input type="date" class="form-input" name="effective_date" 
                                   value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}">
                            @error('effective_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.review_date') }}</label>
                            <input type="date" class="form-input" name="review_date" 
                                   value="{{ old('review_date', $document->review_date?->format('Y-m-d')) }}">
                            @error('review_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.expiry_date') }}</label>
                            <input type="date" class="form-input" name="expiry_date" 
                                   value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                            @error('expiry_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.status') }} *</label>
                        <select class="form-select" name="status" required>
                            <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>{{ __('messages.status_draft') }}</option>
                            <option value="review" {{ old('status', $document->status) == 'review' ? 'selected' : '' }}>{{ __('messages.status_review') }}</option>
                            <option value="approved" {{ old('status', $document->status) == 'approved' ? 'selected' : '' }}>{{ __('messages.status_approved') }}</option>
                            <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>{{ __('messages.status_archived') }}</option>
                        </select>
                        @error('status')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <button type="submit" class="btn btn-primary">{{ __('messages.update_document') }}</button>
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Archive Form -->
        <form method="POST" action="{{ route('documents.destroy', $document) }}" 
              onsubmit="return confirm('{{ __('messages.confirm_archive') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-secondary" style="width: 100%; background: rgba(239, 68, 68, 0.1); color: var(--error); border: 1px solid var(--error);">
                {{ __('messages.archive_document') }}
            </button>
        </form>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        [style*="max-width: 800px"] {
            max-width: 100% !important;
        }
    }
</style>
@endsection
