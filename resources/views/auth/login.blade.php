<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.login') }} - {{ __('messages.fsma_204_food_traceability_system') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        /* Enhanced marketing section with better styling */
        .company-info {
            padding: 2.5rem;
            display: none;
        }
        
        @media (min-width: 768px) {
            .company-info {
                display: block;
            }
        }
        
        .company-logo {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .company-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }
        
        .company-tagline {
            color: #9ca3af;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
            line-height: 1.7;
        }
        
        .badge {
            display: inline-block;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #60a5fa;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.75rem;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
        }
        
        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-title {
            color: #3b82f6;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .feature-desc {
            color: #9ca3af;
            font-size: 0.8rem;
            line-height: 1.4;
        }
        
        .company-details {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .detail-item {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            color: #6b7280;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            color: #e5e7eb;
            font-size: 0.9rem;
        }
        
        .login-card {
            background: #111111;
            border: 1px solid #2a2a2a;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: #3b82f6;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            text-align: center;
            color: #a0a0a0;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #a0a0a0;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 0.5rem;
            color: #ffffff;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        /* Updated responsive breakpoints for better mobile experience */
        @media (max-width: 767px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                gap: 0;
                max-width: 400px;
            }
            
            .login-card {
                padding: 2rem 1.5rem;
            }
        }
        
        @media (min-width: 768px) and (max-width: 968px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <!-- Enhanced marketing section with features grid -->
        <div class="company-info">
            <div class="badge">{{ __('messages.first_vietnam_platform_fda_compliant') }}</div>
            <div class="company-logo">VEXIM GLOBAL</div>
            <div class="company-name">{{ __('messages.vexim_global_company_limited') }}</div>
            <div class="company-tagline">
                {{ __('messages.vexim_global_leading_provider') }}
            </div>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div class="feature-title">{{ __('messages.fully_compliant') }}</div>
                    <div class="feature-desc">{{ __('messages.meet_all_fda_requirements') }}</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">🌐</div>
                    <div class="feature-title">{{ __('messages.global_compatibility') }}</div>
                    <div class="feature-desc">{{ __('messages.gs1_barcode_qr_management') }}</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <div class="feature-title">{{ __('messages.enterprise_security') }}</div>
                    <div class="feature-desc">{{ __('messages.bank_level_encryption') }}</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <div class="feature-title">{{ __('messages.real_time_tracking') }}</div>
                    <div class="feature-desc">{{ __('messages.instant_traceability_updates') }}</div>
                </div>
            </div>
            
            <div class="company-details">
                <div class="detail-item">
                    <div class="detail-label">{{ __('messages.email') }}</div>
                    <div class="detail-value">contact@veximglobal.com</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">{{ __('messages.phone') }}</div>
                    <div class="detail-value">+84-73-685-634</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">{{ __('messages.address') }}</div>
                    <div class="detail-value">25/6/51 Ngoa Long, Tay Tuu, Hanoi, Vietnam</div>
                </div>
            </div>
        </div>
        
        <!-- Login form remains the same -->
        <div class="login-card">
            <div class="logo">FSMA 204</div>
            <div class="subtitle">{{ __('messages.food_traceability_system') }}</div>
            
            @if($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.password') }}</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                
                <button type="submit" class="btn-login">{{ __('messages.sign_in') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
