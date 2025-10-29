<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Traceability Report - {{ $results['query_tlc'] }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
        }
        .info-box {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-box h3 {
            margin-top: 0;
            font-size: 12pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }
        .timeline-item {
            margin-bottom: 15px;
            padding-left: 20px;
            border-left: 3px solid #4a5568;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Traceability Report</h1>
        <p>TLC: <strong>{{ $results['query_tlc'] }}</strong></p>
        <p>Generated: {{ now()->format('F d, Y H:i:s') }}</p>
    </div>

    <div class="info-box">
        <h3>Current Record Information</h3>
        <table>
            <tr>
                <th style="width: 30%;">Product</th>
                <td>{{ $results['record']->product->product_name }}</td>
            </tr>
            <tr>
                <th>SKU</th>
                <td>{{ $results['record']->product->sku }}</td>
            </tr>
            <tr>
                <th>Lot Code</th>
                <td>{{ $results['record']->lot_code }}</td>
            </tr>
            <tr>
                <th>Quantity</th>
                <td>{{ $results['record']->quantity }} {{ $results['record']->unit }}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{{ $results['record']->location->location_name }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($results['record']->status) }}</td>
            </tr>
        </table>
    </div>

    @if(isset($results['gs1_data']))
    <div class="info-box">
        <h3>GS1 Standards Information</h3>
        <table>
            <tr>
                <th style="width: 30%;">GS1 Digital Link</th>
                <td style="font-size: 8pt;">{{ $results['gs1_data']['digital_link'] }}</td>
            </tr>
            <tr>
                <th>GS1-128 Barcode</th>
                <td>{{ $results['gs1_data']['gs1_128'] }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if(count($results['events']) > 0)
    <div class="info-box">
        <h3>Traceability Timeline</h3>
        @foreach($results['events'] as $event)
        <div class="timeline-item">
            <strong>{{ ucfirst($event->event_type) }}</strong> - {{ $event->event_date->format('F d, Y H:i') }}<br>
            Location: {{ $event->location->location_name }}<br>
            @if($event->partner)
            Partner: {{ $event->partner->partner_name }}<br>
            @endif
            Quantity: {{ $event->quantity }} {{ $event->unit }}<br>
            Created by: {{ $event->creator->full_name }}
        </div>
        @endforeach
    </div>
    @endif

    @if(in_array($results['direction'], ['backward', 'both']) && count($results['backward']) > 0)
    <div class="info-box">
        <h3>Trace Backward ({{ count($results['backward']) }} records)</h3>
        <table>
            <thead>
                <tr>
                    <th>Event Type</th>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Product</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results['backward'] as $step)
                <tr>
                    <td>{{ ucfirst($step['event']['type']) }}</td>
                    <td>{{ \Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i') }}</td>
                    <td>{{ $step['from']['name'] ?? '-' }}</td>
                    <td>{{ $step['to']['name'] ?? '-' }}</td>
                    <td>{{ $step['record']['product']['name'] ?? '-' }}</td>
                    <td>{{ $step['event']['quantity'] }} {{ $step['event']['unit'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(in_array($results['direction'], ['forward', 'both']) && count($results['forward']) > 0)
    <div class="info-box">
        <h3>Trace Forward ({{ count($results['forward']) }} records)</h3>
        <table>
            <thead>
                <tr>
                    <th>Event Type</th>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Product</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results['forward'] as $step)
                <tr>
                    <td>{{ ucfirst($step['event']['type']) }}</td>
                    <td>{{ \Carbon\Carbon::parse($step['event']['date'])->format('Y-m-d H:i') }}</td>
                    <td>{{ $step['from']['name'] ?? '-' }}</td>
                    <td>{{ $step['to']['name'] ?? '-' }}</td>
                    <td>{{ $step['record']['product']['name'] ?? '-' }}</td>
                    <td>{{ $step['event']['quantity'] }} {{ $step['event']['unit'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>FSMA 204 Food Traceability Platform | Generated on {{ now()->format('F d, Y H:i:s') }}</p>
    </div>
</body>
</html>
