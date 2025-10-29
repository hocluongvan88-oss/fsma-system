@extends('layouts.app')

@section('title', __('messages.document_management'))

@section('content')
<div style="padding: 2rem 0;">
    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">{{ __('messages.document_management') }}</h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">{{ __('messages.manage_documents_description') }}</p>
        </div>
        <a href="{{ route('documents.create') }}" class="btn btn-primary" style="white-space: nowrap;">
            {{ __('messages.upload_document') }}
        </a>
    </div>

    <!-- Alert Section -->
    @if($expiringDocs > 0)
    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
        <span style="font-size: 1.25rem;">⚠️</span>
        <div style="flex: 1;">
            <strong style="color: var(--warning);">{{ __('messages.expiring_documents', ['count' => $expiringDocs]) }}</strong>
            <a href="{{ route('documents.index', ['expiry_status' => 'expiring_soon']) }}" style="color: var(--accent-primary); text-decoration: none; margin-left: 0.5rem;">{{ __('messages.view_them') }} →</a>
        </div>
    </div>
    @endif

    <!-- Filters Section -->
    @include('documents.partials.advanced-filters')

    <!-- Bulk Actions Section -->
    @include('documents.partials.bulk-actions')

    <!-- Stats Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--accent-primary); margin-bottom: 0.5rem;">{{ $documents->total() }}</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ __('messages.total_documents') }}</div>
        </div>
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--success); margin-bottom: 0.5rem;">{{ $documents->where('status', 'approved')->count() }}</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ __('messages.approved') }}</div>
        </div>
        <div class="card" style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning); margin-bottom: 0.5rem;">{{ $expiringDocs }}</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ __('messages.expiring_soon') }}</div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <div style="padding: 1.5rem;">
            @if($documents->count() > 0)
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color);">
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase; width: 40px;">
                                    <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" style="cursor: pointer;">
                                </th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.doc_number') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.title') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.type') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.status') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.file_size') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.expiry_date') }}</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.uploaded_by') }}</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--text-secondary); font-size: 0.875rem; text-transform: uppercase;">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s;">
                                <td style="padding: 1rem; text-align: center;">
                                    <input type="checkbox" id="doc-checkbox-{{ $doc->id }}" onchange="toggleDocumentSelection({{ $doc->id }})" style="cursor: pointer;">
                                </td>
                                <td style="padding: 1rem; color: var(--accent-primary); font-weight: 600;">{{ $doc->doc_number }}</td>
                                <td style="padding: 1rem;">
                                    <a href="{{ route('documents.show', $doc) }}" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">
                                        {{ $doc->title }}
                                    </a>
                                    @if($doc->isExpired())
                                        <span style="display: inline-block; background: rgba(239, 68, 68, 0.1); color: var(--error); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; margin-left: 0.5rem; font-weight: 600;">{{ __('messages.expired') }}</span>
                                    @elseif($doc->needsReview())
                                        <span style="display: inline-block; background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.75rem; margin-left: 0.5rem; font-weight: 600;">{{ __('messages.needs_review') }}</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; background: rgba(59, 130, 246, 0.1); color: var(--accent-primary); padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600;">
                                        {{ str_replace('_', ' ', ucwords($doc->type)) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem;">
                                    @php
                                        $statusColors = [
                                            'draft' => ['bg' => 'rgba(160, 160, 160, 0.1)', 'text' => 'var(--text-secondary)'],
                                            'review' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => 'var(--warning)'],
                                            'approved' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => 'var(--success)'],
                                            'archived' => ['bg' => 'rgba(107, 114, 128, 0.1)', 'text' => 'var(--text-muted)']
                                        ];
                                        $colors = $statusColors[$doc->status] ?? $statusColors['draft'];
                                    @endphp
                                    <span style="display: inline-block; background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600;">
                                        {{ __('messages.status_' . $doc->status) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $doc->file_size_human }}</td>
                                <td style="padding: 1rem;">
                                    @if($doc->expiry_date)
                                        <span style="color: {{ $doc->isExpired() ? 'var(--error)' : 'var(--text-primary)' }}; font-weight: {{ $doc->isExpired() ? '600' : '400' }};">
                                            {{ $doc->expiry_date->format('Y-m-d') }}
                                        </span>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; color: var(--text-secondary);">{{ $doc->uploader->name }}</td>
                                <td style="padding: 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <a href="{{ route('documents.show', $doc) }}" 
                                           class="btn btn-secondary" 
                                           title="{{ __('messages.view') }}"
                                           style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                            {{ __('messages.view') }}
                                        </a>
                                        <a href="{{ route('documents.download', $doc) }}" 
                                           class="btn btn-secondary" 
                                           title="{{ __('messages.download') }}"
                                           style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                            {{ __('messages.download') }}
                                        </a>
                                        <a href="{{ route('documents.edit', $doc) }}" 
                                           class="btn btn-secondary" 
                                           title="{{ __('messages.edit') }}"
                                           style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                            {{ __('messages.edit') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                    {{ $documents->links() }}
                </div>
            @else
                <div style="text-align: center; padding: 3rem 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">{{ __('messages.no_documents_found') }}</p>
                    <a href="{{ route('documents.create') }}" class="btn btn-primary">{{ __('messages.upload_first_document') }}</a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        table {
            font-size: 0.875rem !important;
        }
        
        th, td {
            padding: 0.75rem !important;
        }
        
        .btn {
            padding: 0.375rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        
        h1 {
            font-size: 1.5rem !important;
        }
        
        th:nth-child(4),
        td:nth-child(4),
        th:nth-child(6),
        td:nth-child(6),
        th:nth-child(7),
        td:nth-child(7) {
            display: none;
        }
    }

    tbody tr:hover {
        background: rgba(59, 130, 246, 0.05);
    }
</style>
@endsection
