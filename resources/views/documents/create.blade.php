@extends('layouts.app')

@section('title', 'Upload Document')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-4">
                <h1 class="h3">Upload New Document</h1>
                <p class="text-muted">Upload traceability plans, SOPs, or other compliance documents</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="doc_number" class="form-label">Document Number *</label>
                            <input type="text" class="form-control @error('doc_number') is-invalid @enderror" 
                                   id="doc_number" name="doc_number" 
                                   placeholder="e.g., TP-001, SOP-002"
                                   value="{{ old('doc_number') }}" required>
                            @error('doc_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" 
                                   value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Document Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                <option value="">Select type...</option>
                                <option value="traceability_plan" {{ old('type') == 'traceability_plan' ? 'selected' : '' }}>Traceability Plan</option>
                                <option value="sop" {{ old('type') == 'sop' ? 'selected' : '' }}>Standard Operating Procedure (SOP)</option>
                                <option value="fda_correspondence" {{ old('type') == 'fda_correspondence' ? 'selected' : '' }}>FDA Correspondence</option>
                                <option value="training_material" {{ old('type') == 'training_material' ? 'selected' : '' }}>Training Material</option>
                                <option value="audit_report" {{ old('type') == 'audit_report' ? 'selected' : '' }}>Audit Report</option>
                                <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">File * (Max 10MB)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" required>
                            <small class="form-text text-muted">
                                Supported formats: PDF, DOC, DOCX, XLS, XLSX
                            </small>
                            @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="effective_date" class="form-label">Effective Date</label>
                                <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                       id="effective_date" name="effective_date" 
                                       value="{{ old('effective_date') }}">
                                @error('effective_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="review_date" class="form-label">Review Date</label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" 
                                       id="review_date" name="review_date" 
                                       value="{{ old('review_date') }}">
                                @error('review_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" 
                                       value="{{ old('expiry_date') }}">
                                @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Document
                            </button>
                            <a href="{{ route('documents.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
