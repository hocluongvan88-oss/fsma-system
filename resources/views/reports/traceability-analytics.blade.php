@extends('layouts.app')

@section('title', __('messages.traceability_analytics'))

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">{{ __('messages.traceability_analytics') }}</h2>
        <a href="{{ route('reports.traceability') }}" class="btn btn-secondary">{{ __('messages.back_to_reports') }}</a>
    </div>

    <form method="GET" action="{{ route('reports.traceability.analytics') }}" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.date_from') }}</label>
                <input type="date" name="date_from" class="form-input" value="{{ $dateFrom }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.date_to') }}</label>
                <input type="date" name="date_to" class="form-input" value="{{ $dateTo }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('messages.apply_filters') }}</button>
    </form>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">{{ __('messages.total_queries') }}</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($queryStats->total_queries) }}</div>
        </div>
        
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">{{ __('messages.unique_records') }}</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($queryStats->unique_records) }}</div>
        </div>
        
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 0.5rem; color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">{{ __('messages.unique_users') }}</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($queryStats->unique_ips) }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.queries_by_day') }}</h3>
            <canvas id="queriesByDayChart" style="max-height: 300px;"></canvas>
        </div>
        
        <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem;">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.query_types') }}</h3>
            <canvas id="queryTypesChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <div style="padding: 1.5rem; background: var(--bg-secondary); border-radius: 0.5rem; margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.most_queried_products') }}</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.rank') }}</th>
                        <th>{{ __('messages.product_name') }}</th>
                        <th>{{ __('messages.query_count') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $product)
                    <tr>
                        <td><strong>#{{ $index + 1 }}</strong></td>
                        <td>{{ $product->product_name }}</td>
                        <td>{{ number_format($product->query_count) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const queriesByDayData = @json($queriesByDay);
    const queryTypesData = @json($queryTypes);
    const directionsData = @json($directions);

    new Chart(document.getElementById('queriesByDayChart'), {
        type: 'line',
        data: {
            labels: queriesByDayData.map(d => d.date),
            datasets: [{
                label: '{{ __('messages.queries') }}',
                data: queriesByDayData.map(d => d.count),
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('queryTypesChart'), {
        type: 'doughnut',
        data: {
            labels: queryTypesData.map(d => d.query_type),
            datasets: [{
                data: queryTypesData.map(d => d.count),
                backgroundColor: ['#667eea', '#f093fb', '#4facfe']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endsection
