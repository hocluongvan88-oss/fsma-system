@extends('layouts.app')

@section('title', 'Edit Document')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-4">
                <h1 class="h3">Edit Document</h1>
                <p class="text-muted">Update document metadata and settings</p>
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

            <div class="card mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('documents.update', $document) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Document Number</label>
                            <input type="text" class="form-control" value="{{ $document->doc_number }}" disabled>
                            <small class="form-text text-muted">Document number cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" 
                                   value="{{ old('title', $document->title) }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Document Type *</label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                <option value="traceability_plan" {{ old('type', $document->type) == 'traceability_plan' ? 'selected' : '' }}>Traceability Plan</option>
                                <option value="sop" {{ old('type', $document->type) == 'sop' ? 'selected' : '' }}>Standard Operating Procedure (SOP)</option>
                                <option value="fda_correspondence" {{ old('type', $document->type) == 'fda_correspondence' ? 'selected' : '' }}>FDA Correspondence</option>
                                <option value="training_material" {{ old('type', $document->type) == 'training_material' ? 'selected' : '' }}>Training Material</option>
                                <option value="audit_report" {{ old('type', $document->type) == 'audit_report' ? 'selected' : '' }}>Audit Report</option>
                                <option value="other" {{ old('type', $document->type) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $document->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="effective_date" class="form-label">Effective Date</label>
                                <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                       id="effective_date" name="effective_date" 
                                       value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}">
                                @error('effective_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="review_date" class="form-label">Review Date</label>
                                <input type="date" class="form-control @error('review_date') is-invalid @enderror" 
                                       id="review_date" name="review_date" 
                                       value="{{ old('review_date', $document->review_date?->format('Y-m-d')) }}">
                                @error('review_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                       id="expiry_date" name="expiry_date" 
                                       value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}">
                                @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status', $document->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="review" {{ old('status', $document->status) == 'review' ? 'selected' : '' }}>Review</option>
                                <option value="approved" {{ old('status', $document->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Document
                            </button>
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

             Delete Form (separate) 
            <form method="POST" action="{{ route('documents.destroy', $document) }}" 
                  onsubmit="return confirm('Are you sure you want to archive this document?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-archive"></i> Archive Document
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
