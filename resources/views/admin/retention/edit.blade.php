@extends('layouts.app')

@section('title', __('messages.edit_retention_policy'))

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; gap: 2rem;">
        <!-- Main Form -->
        <div style="flex: 1;">
            <div class="card">
                <div style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">Edit Retention Policy</h2>
                    <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                        Update retention policy configuration for {{ $policy->policy_name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.retention.update', $policy->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Policy Name *</label>
                        <input type="text" name="policy_name" class="form-input" value="{{ old('policy_name', $policy->policy_name) }}" required>
                        @error('policy_name')
                            <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Data Type *</label>
                        <select name="data_type" class="form-select" required>
                            <option value="">Select data type</option>
                            @foreach($dataTypes as $type)
                                <option value="{{ $type['value'] }}" {{ old('data_type', $policy->data_type) === $type['value'] ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('data_type')
                            <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Retention Period (Months) *</label>
                        <input type="number" name="retention_months" class="form-input" value="{{ old('retention_months', $policy->retention_months) }}" min="27" max="120" required>
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">
                            FSMA 204 requires minimum 27 months retention for non-protected data
                        </span>
                        @error('retention_months')
                            <span style="color: var(--error); font-size: 0.875rem; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="backup_before_deletion" value="1" {{ old('backup_before_deletion', $policy->backup_before_deletion) ? 'checked' : '' }} style="width: 18px; height: 18px; margin-right: 0.75rem;">
                            <span style="color: var(--text-secondary);">Create encrypted backup before deletion</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $policy->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; margin-right: 0.75rem;">
                            <span style="color: var(--text-secondary);">Policy is active</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" rows="4">{{ old('description', $policy->description) }}</textarea>
                        @error('description')
                            <span style="color: var(--error); font-size: 0.875rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                        <a href="{{ route('admin.retention.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Policy</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Side Panel -->
        <div style="width: 350px;">
            <!-- Policy Impact Preview -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 1rem;">
                    Policy Impact
                </h3>
                <div style="space-y: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Records to delete:</span>
                        <span style="font-weight: 600; color: var(--error);">{{ number_format($impactPreview['records_to_delete']) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Estimated storage freed:</span>
                        <span style="font-weight: 600;">{{ $impactPreview['storage_freed'] }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Backup size:</span>
                        <span style="font-weight: 600;">{{ $impactPreview['backup_size'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Execution History -->
            <div class="card">
                <h3 style="font-size: 0.875rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 1rem;">
                    Execution History
                </h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    @forelse($executionHistory as $execution)
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.875rem; font-weight: 500;">{{ $execution->records_deleted }} records</span>
                            @if($execution->status === 'success')
                                <span class="badge badge-success">Success</span>
                            @else
                                <span class="badge badge-error">Failed</span>
                            @endif
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">
                            {{ $execution->executed_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; color: var(--text-muted); padding: 2rem; font-size: 0.875rem;">
                        No execution history
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
