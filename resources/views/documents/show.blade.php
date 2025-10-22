@extends('layouts.app')

@section('title', $document->title)

@section('content')
<div class="container">
    <div class="mb-4">
        <a href="{{ route('documents.index') }}" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Documents
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Document Details</h5>
                    <div>
                        @php
                            $statusColors = [
                                'draft' => 'secondary',
                                'review' => 'warning',
                                'approved' => 'success',
                                'archived' => 'dark'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$document->status] }}">
                            {{ ucfirst($document->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 200px;">Document Number:</th>
                            <td><strong>{{ $document->doc_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Title:</th>
                            <td>{{ $document->title }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ str_replace('_', ' ', ucwords($document->type)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $document->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Version:</th>
                            <td>{{ $document->version }}</td>
                        </tr>
                        <tr>
                            <th>File:</th>
                            <td>
                                {{ $document->file_name }}
                                <span class="text-muted">({{ $document->file_size_human }})</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Effective Date:</th>
                            <td>{{ $document->effective_date?->format('Y-m-d') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Review Date:</th>
                            <td>
                                {{ $document->review_date?->format('Y-m-d') ?? '-' }}
                                @if($document->needsReview())
                                <span class="badge bg-warning">Needs Review</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Expiry Date:</th>
                            <td>
                                @if($document->expiry_date)
                                    <span class="{{ $document->isExpired() ? 'text-danger' : '' }}">
                                        {{ $document->expiry_date->format('Y-m-d') }}
                                    </span>
                                    @if($document->isExpired())
                                    <span class="badge bg-danger">Expired</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Uploaded By:</th>
                            <td>{{ $document->uploader->name }} on {{ $document->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @if($document->approved_by)
                        <tr>
                            <th>Approved By:</th>
                            <td>{{ $document->approver->name }} on {{ $document->approved_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Download
                        </a>
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        @if($document->canBeApproved() && auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('documents.approve', $document) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                        </form>
                        @endif
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#newVersionModal">
                            <i class="bi bi-file-earmark-plus"></i> New Version
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Version History</h5>
                </div>
                <div class="card-body">
                    @if($document->versions->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($document->versions as $version)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>Version {{ $version->version }}</strong>
                                    <p class="mb-1 small text-muted">
                                        {{ $version->created_at->format('Y-m-d H:i') }}
                                    </p>
                                    <p class="mb-0 small">
                                        By {{ $version->creator->name }}
                                    </p>
                                    @if($version->change_notes)
                                    <p class="mb-0 small mt-1">
                                        <em>{{ $version->change_notes }}</em>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">No previous versions</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

 New Version Modal 
<div class="modal fade" id="newVersionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('documents.new-version', $document) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload New Version</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">New File *</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label for="change_notes" class="form-label">Change Notes *</label>
                        <textarea class="form-control" id="change_notes" name="change_notes" 
                                  rows="3" required placeholder="Describe what changed in this version..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload New Version</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
