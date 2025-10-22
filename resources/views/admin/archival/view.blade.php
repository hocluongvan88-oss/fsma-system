@extends('layouts.app')

@section('title', __('messages.archived_data') . ' - ' . ucfirst(str_replace('_', ' ', $dataType)))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">
                {{ __('messages.archived_data') }}: {{ ucfirst(str_replace('_', ' ', $dataType)) }}
            </h2>
            <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.875rem;">
                {{ __('messages.archived_data_description') }}
            </p>
        </div>
        <a href="{{ route('admin.archival.index') }}" class="btn btn-secondary">
            {{ __('messages.back_to_dashboard') }}
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.original_id') }}</th>
                    <th>{{ __('messages.archived_at') }}</th>
                    <th>{{ __('messages.created_at') }}</th>
                    <th>{{ __('messages.details') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archivedData as $record)
                    <tr>
                        <td style="font-family: monospace;">#{{ $record->original_id }}</td>
                        <td style="font-size: 0.875rem;">{{ \Carbon\Carbon::parse($record->archived_at)->format('M d, Y H:i') }}</td>
                        <td style="font-size: 0.875rem;">{{ \Carbon\Carbon::parse($record->created_at)->format('M d, Y H:i') }}</td>
                        <td>
                            <button onclick="viewDetails({{ json_encode($record) }})" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                {{ __('messages.view_details') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            {{ __('messages.no_archived_data') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $archivedData->links() }}
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 50;">
    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.record_details') }}</h3>
        <pre id="detailsContent" style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; overflow-x: auto; font-size: 0.875rem;"></pre>
        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
            <button onclick="closeDetailsModal()" class="btn btn-secondary">
                {{ __('messages.close') }}
            </button>
        </div>
    </div>
</div>

<script>
function viewDetails(record) {
    document.getElementById('detailsContent').textContent = JSON.stringify(record, null, 2);
    document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetailsModal();
});
</script>
@endsection
