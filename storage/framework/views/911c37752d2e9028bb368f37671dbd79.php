<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- Added locale meta tag for proper language detection -->
    <meta name="locale" content="<?php echo e(app()->getLocale()); ?>">
    <?php if(config('locales.available_locales')): ?>
    <meta name="available-locales" content="<?php echo e(implode(',', array_keys(config('locales.available_locales')))); ?>">
    <?php endif; ?>
    <title><?php echo $__env->yieldContent('title', 'FSMA 204 Traceability System'); ?></title>
    
    <!-- Added Tailwind CSS CDN link for proper styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #111111;
            --bg-tertiary: #1a1a1a;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --text-muted: #666666;
            --accent-primary: #3b82f6;
            --accent-hover: #2563eb;
            --border-color: #2a2a2a;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            /* Mobile optimization - prevent text zoom on iOS */
            -webkit-text-size-adjust: 100%;
            font-size: 16px;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--accent-primary);
            /* Added link styling for logo */
            text-decoration: none;
            display: block;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        
        .logo:hover {
            opacity: 0.8;
        }
        
        .nav-section {
            margin-bottom: 2rem;
        }
        
        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
            /* Mobile optimization - larger touch targets */
            min-height: 44px;
        }
        
        .nav-link:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        
        .nav-link.active {
            background: var(--accent-primary);
            color: white;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            /* Mobile optimization - larger touch targets */
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--accent-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--accent-hover);
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--bg-secondary);
        }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        tr:hover {
            background: var(--bg-tertiary);
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge-error { background: rgba(239, 68, 68, 0.1); color: var(--error); }
        .badge-info { background: rgba(59, 130, 246, 0.1); color: var(--accent-primary); }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-size: 0.875rem;
            /* Mobile optimization - prevent zoom on iOS */
            font-size: 16px;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--accent-primary);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--error);
            color: var(--error);
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            /* Add overflow hidden to prevent oversized elements from breaking layout */
            overflow: hidden;
            align-items: center;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--text-secondary);
            text-decoration: none;
            /* Mobile optimization - larger touch targets */
            min-height: 44px;
            min-width: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            /* Prevent flex items from growing */
            flex-shrink: 0 !important;
            flex-grow: 0 !important;
        }
        
        /* Ultra-aggressive SVG size constraints for pagination - using multiple selectors */
        .pagination svg,
        .pagination a svg,
        .pagination span svg,
        .pagination button svg,
        .pagination * svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            min-width: 16px !important;
            min-height: 16px !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }
        
        .pagination button {
            width: auto;
            height: auto;
            padding: 0.5rem 0.75rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            color: var(--text-secondary);
            cursor: pointer;
            min-height: 44px;
            min-width: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            flex-grow: 0;
        }
        
        .pagination a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .pagination .active {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }
        
        /* Global rule to prevent any oversized SVG from breaking the layout */
        svg {
            max-width: 100%;
            max-height: 100%;
        }
        
        /* Specifically target standalone large SVGs that might appear outside containers */
        body > svg,
        .main-content > svg {
            display: none !important;
        }
        
        /* Hide any SVG larger than 200px (these are likely rendering errors) */
        svg[width*="00"],
        svg[height*="00"] {
            width: 16px !important;
            height: 16px !important;
        }
        
        /* Mobile optimization - prevent text zoom on iOS */
        @media (max-width: 768px) {
            /* Show mobile menu button on mobile only */
            .mobile-menu-btn {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1001;
                background: var(--accent-primary);
                color: white;
                border: none;
                border-radius: 0.5rem;
                padding: 0.75rem;
                cursor: pointer;
                width: 44px;
                height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }
            
            .mobile-menu-btn:hover {
                background: var(--accent-hover);
            }
            
            /* Improved sidebar styling for mobile with better animations */
            .sidebar {
                width: 280px;
                position: fixed;
                height: 100vh;
                left: -280px;
                top: 0;
                transition: left 0.3s ease;
                z-index: 1000;
                border-right: 1px solid var(--border-color);
                border-bottom: none;
                box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            }
            
            .sidebar.active {
                left: 0;
            }
            
            /* Added overlay styling for click-outside functionality */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                cursor: pointer;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                padding: 4rem 1rem 1rem 1rem;
                width: 100%;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .user-menu {
                width: 100%;
                justify-content: space-between;
            }
            
            /* Better table mobile handling - card style */
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 100%;
                font-size: 0.875rem;
            }
            
            th, td {
                padding: 0.75rem 0.5rem;
                white-space: nowrap;
            }
            
            /* Stack stat cards on mobile */
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
            
            /* Better form spacing on mobile */
            .form-group {
                margin-bottom: 1.25rem;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            /* Better card spacing */
            .card {
                padding: 1rem;
                margin-bottom: 1rem;
            }
        }
        
        /* Hide mobile menu button on desktop */
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none !important;
            }
        }
        
        /* Tablet optimization */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 220px;
            }
            
            .main-content {
                margin-left: 220px;
            }
        }
        
        /* PWA support - hide scrollbar on mobile */
        @media (max-width: 768px) {
            .sidebar::-webkit-scrollbar {
                display: none;
            }
            
            .sidebar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        }
    </style>
    
    <!-- Added JavaScript localization support -->
    <script>
        window.AppLocale = {
            current: '<?php echo e(app()->getLocale()); ?>',
            available: <?php echo json_encode(config('locales.available_locales') ?? [], 15, 512) ?>,
            translations: {},
            
            loadTranslations: function() {
                fetch(`/api/translations/${this.current}`)
                    .then(response => response.json())
                    .then(data => {
                        this.translations = data;
                    })
                    .catch(error => {
                        console.error('[v0] Failed to load translations:', error);
                    });
            },
            
            trans: function(key, replacements = {}) {
                let translation = this.translations[key] || key;
                
                Object.keys(replacements).forEach(placeholder => {
                    translation = translation.replace(`:${placeholder}`, replacements[placeholder]);
                });
                
                return translation;
            },
            
            formatDate: function(date, format = 'short') {
                const locale = this.available[this.current];
                if (!locale) return date;
                
                const dateObj = new Date(date);
                
                const options = {
                    short: { year: 'numeric', month: '2-digit', day: '2-digit' },
                    long: { year: 'numeric', month: 'long', day: 'numeric' },
                    full: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
                };
                
                return dateObj.toLocaleDateString(locale.code, options[format] || options.short);
            },
            
            formatNumber: function(number, decimals = 0) {
                const locale = this.available[this.current];
                if (!locale) return number;
                
                return new Intl.NumberFormat(locale.code, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format(number);
            },
            
            formatCurrency: function(amount, currency = 'USD') {
                const locale = this.available[this.current];
                if (!locale) return amount;
                
                return new Intl.NumberFormat(locale.code, {
                    style: 'currency',
                    currency: currency
                }).format(amount);
            }
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            window.AppLocale.loadTranslations();
        });
    </script>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php echo $__env->make('components.blocking-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>
    
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>
    
    <div class="app-container">
        <aside class="sidebar" id="sidebar">
            <!-- Wrapped logo in link to homepage -->
            <a href="<?php echo e(route('dashboard')); ?>" class="logo">FSMA 204</a>
            
            <nav>
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('messages.main')); ?></div>
                    <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <?php echo e(__('messages.dashboard')); ?>

                    </a>
                    <a href="<?php echo e(route('pricing')); ?>" class="nav-link <?php echo e(request()->routeIs('pricing') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <?php echo e(__('messages.pricing')); ?>

                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('messages.master_data')); ?></div>
                    <a href="<?php echo e(route('master-data.products.index')); ?>" class="nav-link <?php echo e(request()->routeIs('master-data.products.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <?php echo e(__('messages.products')); ?>

                    </a>
                    <a href="<?php echo e(route('master-data.locations.index')); ?>" class="nav-link <?php echo e(request()->routeIs('master-data.locations.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <?php echo e(__('messages.locations')); ?>

                    </a>
                    <a href="<?php echo e(route('master-data.partners.index')); ?>" class="nav-link <?php echo e(request()->routeIs('master-data.partners.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M17 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            <path d="M2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M7 21h10"></path>
                            <path d="M12 3v18"></path>
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                        </svg>
                        <?php echo e(__('messages.partners')); ?>

                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('messages.cte_events')); ?></div>
                    <a href="<?php echo e(route('cte.receiving')); ?>" class="nav-link <?php echo e(request()->routeIs('cte.receiving') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M16 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M2 16l3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1z"></path>
                            <path d="M7 21h10"></path>
                            <path d="M12 3v18"></path>
                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"></path>
                        </svg>
                        <?php echo e(__('messages.receiving')); ?>

                    </a>
                    <a href="<?php echo e(route('cte.transformation')); ?>" class="nav-link <?php echo e(request()->routeIs('cte.transformation') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                            <path d="M1 12h6m6 0h6"></path>
                            <path d="m4.93 19.07 4.24-4.24m5.66-5.66 4.24-4.24"></path>
                        </svg>
                        <?php echo e(__('messages.transformation')); ?>

                    </a>
                    <a href="<?php echo e(route('cte.shipping')); ?>" class="nav-link <?php echo e(request()->routeIs('cte.shipping') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <rect x="1" y="3" width="15" height="13"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                        <?php echo e(__('messages.shipping')); ?>

                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(__('messages.reports')); ?></div>
                    <a href="<?php echo e(route('reports.traceability')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.traceability') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21-4.35-4.35"></path>
                        </svg>
                        <?php echo e(__('messages.traceability_query')); ?>

                    </a>
                    <a href="<?php echo e(route('reports.audit-log')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.audit-log') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <?php echo e(__('messages.audit_log')); ?>

                    </a>
                </div>
                
                <?php if(auth()->user()->isManager()): ?>
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo e(auth()->user()->isAdmin() ? __('messages.admin') : __('messages.management')); ?></div>
                    
                    <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.packages.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.packages.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <?php echo e(__('messages.packages')); ?>

                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <?php echo e(__('messages.user_management')); ?>

                    </a>
                    
                    <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.retention.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.retention.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        <?php echo e(__('messages.data_retention')); ?>

                    </a>
                    <!-- Added Archival menu item -->
                    <a href="<?php echo e(route('admin.archival.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.archival.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline>
                            <polyline points="7.5 19.79 7.5 14.6 3 12"></polyline>
                            <polyline points="21 12 16.5 14.6 16.5 19.79"></polyline>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <?php echo e(__('messages.archival')); ?>

                    </a>
                    <!-- Added E-Signature menu item -->
                    <a href="<?php echo e(route('admin.e-signatures.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.e-signatures.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                        </svg>
                        <?php echo e(__('messages.e_signatures')); ?>

                    </a>
                    <a href="<?php echo e(route('certificates.index')); ?>" class="nav-link <?php echo e(request()->routeIs('certificates.*') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <?php echo e(__('messages.certificates')); ?>

                    </a>
                    <a href="<?php echo e(route('admin.compliance')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.compliance') ? 'active' : ''); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.75rem;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                        <?php echo e(__('messages.compliance_report')); ?>

                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1 class="page-title"><?php echo $__env->yieldContent('title'); ?></h1>
                
                <div class="user-menu">
                    <!-- Added language switcher to header -->
                    <?php echo $__env->make('components.language-switcher-custom', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    
                    <?php echo $__env->make('components.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    
                    <span style="color: var(--text-secondary);"><?php echo e(auth()->user()->full_name); ?></span>
                    <form action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-secondary">Logout</button>
                    </form>
                </div>
            </div>
            
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-error"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
            
            <?php if($errors->any()): ?>
                <div class="alert alert-error">
                    <ul style="margin-left: 1.5rem;">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
    
    <?php echo $__env->make('components.barcode-scanner-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        // Close sidebar when clicking nav link on mobile
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleMobileMenu();
                }
            });
        });
    </script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/fxiasdcg/root/resources/views/layouts/app.blade.php ENDPATH**/ ?>