@extends('layouts.app')

@section('title', 'Document Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Document Management</h1>
            <p class="text-muted">Manage traceability plans, SOPs, and FDA correspondence</p>
        </div>
        <a href="{{ route('documents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Upload Document
        </a>
    </div>

    @if($expiringDocs > 0)
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>{{ $expiringDocs }}</strong> document(s) expiring within 30 days. 
        <a href="{{ route('documents.index', ['expiring' => 1]) }}" class="alert-link">View them</a>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

     Filters 
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Doc number, title..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="traceability_plan" {{ request('type') == 'traceability_plan' ? 'selected' : '' }}>Traceability Plan</option>
                        <option value="sop" {{ request('type') == 'sop' ? 'selected' : '' }}>SOP</option>
                        <option value="fda_correspondence" {{ request('type') == 'fda_correspondence' ? 'selected' : '' }}>FDA Correspondence</option>
                        <option value="training_material" {{ request('type') == 'training_material' ? 'selected' : '' }}>Training Material</option>
                        <option value="audit_report" {{ request('type') == 'audit_report' ? 'selected' : '' }}>Audit Report</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('documents.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

     Documents Table 
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Doc Number</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Effective Date</th>
                            <th>Expiry Date</th>
                            <th>Uploaded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>
                                <strong>{{ $doc->doc_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('documents.show', $doc) }}" class="text-decoration-none">
                                    {{ $doc->title }}
                                </a>
                                @if($doc->isExpired())
                                <span class="badge bg-danger ms-1">Expired</span>
                                @elseif($doc->needsReview())
                                <span class="badge bg-warning ms-1">Needs Review</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ str_replace('_', ' ', ucwords($doc->type)) }}
                                </span>
                            </td>
                            <td>{{ $doc->version }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'review' => 'warning',
                                        'approved' => 'success',
                                        'archived' => 'dark'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$doc->status] }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td>{{ $doc->effective_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>
                                @if($doc->expiry_date)
                                    <span class="{{ $doc->isExpired() ? 'text-danger' : '' }}">
                                        {{ $doc->expiry_date->format('Y-m-d') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $doc->uploader->name }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('documents.show', $doc) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('documents.download', $doc) }}" 
                                       class="btn btn-outline-success" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="{{ route('documents.edit', $doc) }}" 
                                       class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No documents found. <a href="{{ route('documents.create') }}">Upload your first document</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
