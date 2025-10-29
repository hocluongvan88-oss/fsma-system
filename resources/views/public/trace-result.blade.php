<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.traceability_result') }} - {{ $record->tlc }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body {
            background: #f8f9fa;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        
        .info-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #667eea;
        }
        
        .qr-code {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
        }
        
        .branding-footer {
            background: white;
            padding: 2rem;
            text-align: center;
            margin-top: 3rem;
            border-top: 3px solid #667eea;
        }
        
        @media (max-width: 768px) {
            .header-section {
                padding: 1.5rem 0;
            }
            .info-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <i class="bi bi-check-circle"></i> {{ __('messages.product_traced_successfully') }}
                    </h1>
                    <p class="mb-0">TLC: <strong>{{ $record->tlc }}</strong></p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('public.trace') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.new_search') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="row">
            <div class="col-lg-8">
                {{-- Product Information with translations --}}
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="bi bi-box-seam"></i> {{ __('messages.product_information') }}
                    </h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 200px;">{{ __('messages.product_name') }}:</th>
                            <td><strong>{{ $record->product->product_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.sku') }}:</th>
                            <td>{{ $record->product->sku }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.food_traceability_list') }}:</th>
                            <td>
                                @if($record->product->is_ftl)
                                <span class="badge bg-warning">{{ __('messages.yes_enhanced_traceability') }}</span>
                                @else
                                <span class="badge bg-secondary">{{ __('messages.no') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.lot_code') }}:</th>
                            <td>{{ $record->lot_code }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.harvest_pack_date') }}:</th>
                            <td>{{ $record->harvest_date?->format('F d, Y') ?? __('messages.not_available') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.quantity') }}:</th>
                            <td>{{ number_format($record->quantity) }} {{ $record->unit }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.current_location') }}:</th>
                            <td>{{ $record->location->location_name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('messages.status') }}:</th>
                            <td>
                                @php
                                    $statusColors = [
                                        'received' => 'success',
                                        'in_process' => 'warning',
                                        'shipped' => 'info',
                                        'consumed' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}">
                                    {{ __('messages.status_' . $record->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                {{-- Traceability Timeline with translations --}}
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="bi bi-clock-history"></i> {{ __('messages.traceability_timeline') }}
                    </h5>
                    
                    <div class="timeline">
                        @foreach($record->cteEvents->sortBy('event_date') as $event)
                        <div class="timeline-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        @if($event->event_type === 'receiving')
                                        <i class="bi bi-box-arrow-in-down text-success"></i> {{ __('messages.receiving') }}
                                        @elseif($event->event_type === 'transformation')
                                        <i class="bi bi-arrow-left-right text-warning"></i> {{ __('messages.transformation') }}
                                        @elseif($event->event_type === 'shipping')
                                        <i class="bi bi-box-arrow-up text-info"></i> {{ __('messages.shipping') }}
                                        @endif
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        {{ $event->event_date->format('F d, Y') }}
                                    </p>
                                    @if($event->location)
                                    <p class="mb-1">
                                        <i class="bi bi-geo-alt"></i> {{ $event->location->location_name }}
                                    </p>
                                    @endif
                                    @if($event->partner)
                                    <p class="mb-1">
                                        <i class="bi bi-building"></i> {{ $event->partner->partner_name }}
                                    </p>
                                    @endif
                                    @if($event->notes)
                                    <p class="mb-0 small text-muted">{{ $event->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Trace Backward with translations --}}
                @if($traceBack->count() > 0)
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="bi bi-arrow-left-circle"></i> {{ __('messages.source_materials') }}
                    </h5>
                    <div class="list-group">
                        @foreach($traceBack as $source)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $source->tlc }}</strong>
                                    <p class="mb-0 small text-muted">
                                        {{ $source->product->product_name }} - {{ $source->lot_code }}
                                    </p>
                                </div>
                                <a href="{{ route('public.trace.tlc', $source->tlc) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view') }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Trace Forward with translations --}}
                @if($traceForward->count() > 0)
                <div class="info-card">
                    <h5 class="mb-3">
                        <i class="bi bi-arrow-right-circle"></i> {{ __('messages.derived_products') }}
                    </h5>
                    <div class="list-group">
                        @foreach($traceForward as $derived)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $derived->tlc }}</strong>
                                    <p class="mb-0 small text-muted">
                                        {{ $derived->product->product_name }} - {{ $derived->lot_code }}
                                    </p>
                                </div>
                                <a href="{{ route('public.trace.tlc', $derived->tlc) }}" class="btn btn-sm btn-outline-primary">
                                    {{ __('messages.view') }}
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                {{-- QR Code with translations --}}
                <div class="info-card qr-code">
                    <h6 class="mb-3">{{ __('messages.share_this_trace') }}</h6>
                    <img src="{{ $gs1Data['qr_code_url'] }}" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px;">
                    <p class="small text-muted mb-2">{{ __('messages.scan_to_view') }}</p>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyLink()">
                        <i class="bi bi-link"></i> {{ __('messages.copy_link') }}
                    </button>
                </div>

                {{-- GS1 Information with translations --}}
                <div class="info-card">
                    <h6 class="mb-3">
                        <i class="bi bi-upc-scan"></i> {{ __('messages.gs1_standards') }}
                    </h6>
                    <div class="mb-3">
                        <label class="small text-muted">{{ __('messages.gs1_digital_link') }}:</label>
                        <input type="text" class="form-control form-control-sm" 
                               value="{{ $gs1Data['digital_link'] }}" readonly>
                    </div>
                    <div class="mb-0">
                        <label class="small text-muted">{{ __('messages.gs1_128_barcode') }}:</label>
                        <input type="text" class="form-control form-control-sm" 
                               value="{{ $gs1Data['gs1_128'] }}" readonly>
                    </div>
                </div>

                {{-- FDA Compliance with translations --}}
                <div class="info-card">
                    <h6 class="mb-3">
                        <i class="bi bi-shield-check"></i> {{ __('messages.fda_fsma_204_compliant') }}
                    </h6>
                    <p class="small mb-0">
                        {{ __('messages.fda_compliance_description') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Added branding footer --}}
        <div class="branding-footer">
            <h5 class="mb-3">{{ __('messages.powered_by') }}</h5>
            <h4 class="text-primary mb-3">FSMA 204 Food Traceability Platform</h4>
            <p class="text-muted mb-3">
                {{ __('messages.first_vietnam_platform') }}
            </p>
            <div class="mb-3">
                <span class="badge bg-success me-2">
                    <i class="bi bi-shield-check"></i> FDA FSMA 204
                </span>
                <span class="badge bg-info me-2">
                    <i class="bi bi-globe"></i> GS1 Standards
                </span>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-lock-fill"></i> 21 CFR Part 11
                </span>
            </div>
            <p class="small text-muted mb-0">
                {{ __('messages.free_unlimited_lookups') }}
            </p>
        </div>
    </div>

    <script>
        function copyLink() {
            const link = window.location.href;
            navigator.clipboard.writeText(link).then(() => {
                alert('{{ __('messages.link_copied') }}');
            });
        }
    </script>
</body>
</html>
