@extends('layouts.app')

@section('title', __('messages.upload_document'))

@section('content')
<div style="padding: 2rem 0;">
    <div style="max-width: 800px;">
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">{{ __('messages.upload_document') }}</h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 2rem;">{{ __('messages.add_new_document') }}</p>

        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            
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

            <!-- File Upload -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem;">
                    <label class="form-label">{{ __('messages.document_file') }} *</label>
                    <div style="border: 2px dashed var(--border-color); border-radius: 0.5rem; padding: 2rem; text-align: center; background: var(--bg-tertiary); cursor: pointer;" id="dropZone">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                        <p style="margin-bottom: 0.5rem; color: var(--text-primary);">{{ __('messages.drag_drop_file') }}</p>
                        <input type="file" class="form-input" id="file" name="file" required style="display: none;" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <button type="button" class="btn btn-secondary" style="margin-top: 0.5rem;" onclick="document.getElementById('file').click()">{{ __('messages.choose_file') }}</button>
                        <div id="fileInfo" style="margin-top: 1rem; display: none;">
                            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--accent-primary); border-radius: 0.5rem; padding: 0.75rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <span id="fileName" style="color: var(--accent-primary);"></span>
                                <button type="button" onclick="clearFile()" style="background: none; border: none; color: var(--accent-primary); cursor: pointer; font-size: 1.25rem; padding: 0; line-height: 1;">×</button>
                            </div>
                        </div>
                        <small style="display: block; margin-top: 0.5rem; color: var(--text-secondary);">
                            {{ __('messages.supported_formats') }}
                        </small>
                    </div>
                    @error('file')
                    <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Document Information -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.document_information') }}</h3>

                    <!-- Document Number -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.doc_number') }} *</label>
                        <input type="text" name="doc_number" class="form-input" 
                               placeholder="{{ __('messages.doc_number_placeholder') }}"
                               value="{{ old('doc_number') }}" required>
                        @error('doc_number')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Document Type -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.type') }} *</label>
                        <select name="type" class="form-select" required>
                            <option value="">{{ __('messages.select_type') }}</option>
                            <option value="traceability_plan" {{ old('type') == 'traceability_plan' ? 'selected' : '' }}>{{ __('messages.traceability_plan') }}</option>
                            <option value="sop" {{ old('type') == 'sop' ? 'selected' : '' }}>{{ __('messages.sop') }}</option>
                            <option value="fda_correspondence" {{ old('type') == 'fda_correspondence' ? 'selected' : '' }}>{{ __('messages.fda_correspondence') }}</option>
                            <option value="training_material" {{ old('type') == 'training_material' ? 'selected' : '' }}>{{ __('messages.training_material') }}</option>
                            <option value="audit_report" {{ old('type') == 'audit_report' ? 'selected' : '' }}>{{ __('messages.audit_report') }}</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                        </select>
                        @error('type')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Title -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.title') }} *</label>
                        <input type="text" name="title" class="form-input" 
                               placeholder="{{ __('messages.enter_document_title') }}"
                               value="{{ old('title') }}" required>
                        @error('title')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.description') }}</label>
                        <textarea name="description" class="form-textarea" 
                                  placeholder="{{ __('messages.enter_description_optional') }}">{{ old('description') }}</textarea>
                        @error('description')
                        <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Dates Section -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1.5rem;">
                    <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 1rem; text-transform: uppercase;">{{ __('messages.important_dates') }}</h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.effective_date') }}</label>
                            <input type="date" name="effective_date" class="form-input" value="{{ old('effective_date') }}">
                            @error('effective_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.review_date') }}</label>
                            <input type="date" name="review_date" class="form-input" value="{{ old('review_date') }}">
                            @error('review_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">{{ __('messages.expiry_date') }}</label>
                            <input type="date" name="expiry_date" class="form-input" value="{{ old('expiry_date') }}">
                            @error('expiry_date')
                            <small style="color: var(--error); display: block; margin-top: 0.25rem;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--accent-primary); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                <strong style="color: var(--accent-primary); display: block; margin-bottom: 0.5rem;">{{ __('messages.upload_guidelines') }}</strong>
                <ul style="margin: 0; padding-left: 1.25rem; color: var(--text-secondary); font-size: 0.875rem;">
                    <li>{{ __('messages.guideline_1') }}</li>
                    <li>{{ __('messages.guideline_2') }}</li>
                    <li>{{ __('messages.guideline_3') }}</li>
                    <li>{{ __('messages.guideline_4') }}</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary">{{ __('messages.upload_document') }}</button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = 'var(--accent-primary)';
            dropZone.style.background = 'rgba(59, 130, 246, 0.05)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.style.borderColor = 'var(--border-color)';
            dropZone.style.background = 'var(--bg-tertiary)';
        }, false);
    });

    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        handleFiles(files);
    }, false);

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            fileInfo.style.display = 'block';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
    };
});
</script>

<style>
    @media (max-width: 768px) {
        [style*="max-width: 800px"] {
            max-width: 100% !important;
        }
        
        #dropZone {
            padding: 1.5rem 1rem !important;
        }
        
        #dropZone > div:first-child {
            font-size: 2rem !important;
        }
    }
</style>
@endsection
