<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.food_traceability_lookup') }} - FSMA 204</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .trace-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        
        .trace-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        
        .scan-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .branding {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0 0 20px 20px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .branding img {
            max-height: 30px;
            opacity: 0.7;
        }
        
        .badge-vietnam {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-block;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        @media (max-width: 576px) {
            .trace-header {
                padding: 1.5rem 1rem;
            }
            .scan-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="trace-card">
            <div class="trace-header">
                {{-- Added Vietnam first badge --}}
                <div class="badge-vietnam">
                    <i class="bi bi-star-fill"></i> {{ __('messages.first_in_vietnam') }}
                </div>
                <i class="bi bi-qr-code-scan scan-icon"></i>
                <h1 class="h3 mb-2">{{ __('messages.food_traceability_lookup') }}</h1>
                <p class="mb-0">{{ __('messages.enter_or_scan_tlc') }}</p>
            </div>
            
            <div class="p-4">
                @if(isset($error))
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> {{ $error }}
                </div>
                @endif

                <form method="GET" action="{{ route('public.trace') }}">
                    <div class="mb-3">
                        <label for="tlc" class="form-label">{{ __('messages.traceability_lot_code') }} (TLC)</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="tlc" 
                               name="tlc" 
                               placeholder="{{ __('messages.tlc_placeholder') }}"
                               required
                               autofocus>
                        <small class="form-text text-muted">
                            {{ __('messages.find_tlc_on_label') }}
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="bi bi-search"></i> {{ __('messages.trace_product') }}
                    </button>

                    <button type="button" class="btn btn-outline-secondary w-100" onclick="startScanner()">
                        <i class="bi bi-camera"></i> {{ __('messages.scan_qr_code') }}
                    </button>
                </form>

                <hr class="my-4">

                {{-- Added FSMA 204 info section with translation --}}
                <div class="text-center text-muted">
                    <p class="mb-2"><strong>{{ __('messages.what_is_fsma_204') }}</strong></p>
                    <p class="small">
                        {{ __('messages.fsma_204_description') }}
                    </p>
                </div>

                {{-- Added compliance badges --}}
                <div class="text-center mt-4">
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
            </div>

            {{-- Added branding footer --}}
            <div class="branding">
                <p class="small mb-2 text-muted">{{ __('messages.powered_by') }}</p>
                <strong>FSMA 204 Food Traceability Platform</strong>
                <p class="small text-muted mb-0 mt-2">
                    {{ __('messages.free_public_lookup') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Improved mobile scanner with fallback --}}
    <script>
        function startScanner() {
            // Check if device has camera
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('{{ __('messages.camera_not_available') }}');
                return;
            }

            // For mobile devices, use native camera input
            if (/Android|webOS|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.capture = 'environment';
                input.onchange = function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Here you would integrate with a QR code library
                        alert('{{ __('messages.qr_scan_feature_coming_soon') }}');
                    }
                };
                input.click();
            } else {
                alert('{{ __('messages.scan_with_mobile') }}');
            }
        }
    </script>
</body>
</html>
