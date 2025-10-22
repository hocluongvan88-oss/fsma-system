<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\CTEController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ESignatureController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PublicTraceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\LandingPageController; // Added for landing page routes
use App\Http\Controllers\TwoFactorAuthController; // Added for 2FA
use App\Http\Controllers\DigitalCertificateController; // Added for Digital Certificates
use App\Http\Controllers\RetentionPolicyController; // Added for Retention Policy Management
use App\Http\Controllers\EnhancedESignatureController;
use App\Http\Controllers\SignatureRecordTypeController;
use App\Http\Controllers\LanguageController; // Added LanguageController

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/api/translations/{locale}', [LanguageController::class, 'getTranslations'])->name('api.translations');

// Landing Page Routes
Route::get('/demo', [LandingPageController::class, 'show'])->name('landing.fsma204');
Route::post('/api/leads', [LandingPageController::class, 'storeLead'])->name('leads.store');

Route::get('/clear-cache', function() {
    try {
        $results = [];
        
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            $results['config_cache'] = 'Cleared';
        } catch (\Exception $e) {
            $results['config_cache'] = 'Error: ' . $e->getMessage();
        }
        
        try {
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            $results['route_cache'] = 'Cleared';
        } catch (\Exception $e) {
            $results['route_cache'] = 'Error: ' . $e->getMessage();
        }
        
        try {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            $results['view_cache'] = 'Cleared';
        } catch (\Exception $e) {
            $results['view_cache'] = 'Error: ' . $e->getMessage();
        }
        
        try {
            \Illuminate\Support\Facades\Artisan::call('clear-compiled');
            $results['compiled'] = 'Cleared';
        } catch (\Exception $e) {
            $results['compiled'] = 'Error: ' . $e->getMessage();
        }
        
        // Instead, manually clear file cache
        try {
            $cachePath = storage_path('framework/cache/data');
            if (is_dir($cachePath)) {
                $files = glob($cachePath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                $results['file_cache'] = 'Cleared ' . count($files) . ' files';
            } else {
                $results['file_cache'] = 'Cache directory not found';
            }
        } catch (\Exception $e) {
            $results['file_cache'] = 'Error: ' . $e->getMessage();
        }
        
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            try {
                opcache_reset();
                $results['opcache'] = 'Cleared';
            } catch (\Exception $e) {
                $results['opcache'] = 'Error: ' . $e->getMessage();
            }
        } else {
            $results['opcache'] = 'Not available';
        }
        
        // Clear bootstrap cache files
        $bootstrapCache = base_path('bootstrap/cache');
        $bootstrapFiles = [];
        
        if (file_exists($bootstrapCache . '/packages.php')) {
            unlink($bootstrapCache . '/packages.php');
            $bootstrapFiles[] = 'packages.php';
        }
        
        if (file_exists($bootstrapCache . '/services.php')) {
            unlink($bootstrapCache . '/services.php');
            $bootstrapFiles[] = 'services.php';
        }
        
        if (file_exists($bootstrapCache . '/config.php')) {
            unlink($bootstrapCache . '/config.php');
            $bootstrapFiles[] = 'config.php';
        }
        
        if (file_exists($bootstrapCache . '/routes-v7.php')) {
            unlink($bootstrapCache . '/routes-v7.php');
            $bootstrapFiles[] = 'routes-v7.php';
        }
        
        $results['bootstrap_cache'] = count($bootstrapFiles) > 0 
            ? 'Cleared: ' . implode(', ', $bootstrapFiles)
            : 'No files to clear';
        
        return response()->json([
            'thanh_cong' => true,
            'thong_bao' => 'Đã xóa tất cả cache thành công!',
            'chi_tiet' => $results,
            'buoc_tiep_theo' => 'Refresh trang (Ctrl+F5) và thử lại chức năng bị lỗi',
            'luu_y' => 'Đã bỏ qua database cache vì table không tồn tại. Hệ thống sẽ dùng file cache.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'thanh_cong' => false,
            'loi' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'huong_dan' => 'Liên hệ hosting để họ chạy: php artisan optimize:clear'
        ]);
    }
});

Route::get('/regenerate-autoloader', function() {
    try {
        $output = [];
        $returnCode = 0;
        
        // Try to run composer dump-autoload
        $composerPath = base_path('composer.phar');
        $useComposerPhar = file_exists($composerPath);
        
        if ($useComposerPhar) {
            // Use composer.phar if available
            exec("cd " . base_path() . " && php composer.phar dump-autoload 2>&1", $output, $returnCode);
        } else {
            // Try global composer command
            exec("cd " . base_path() . " && composer dump-autoload 2>&1", $output, $returnCode);
        }
        
        $success = $returnCode === 0;
        
        // Also clear Laravel's cached autoload files
        $bootstrapCache = base_path('bootstrap/cache');
        $filesCleared = [];
        
        if (file_exists($bootstrapCache . '/packages.php')) {
            unlink($bootstrapCache . '/packages.php');
            $filesCleared[] = 'packages.php';
        }
        
        if (file_exists($bootstrapCache . '/services.php')) {
            unlink($bootstrapCache . '/services.php');
            $filesCleared[] = 'services.php';
        }
        
        // Clear all Laravel caches
        \Illuminate\Support\Facades\Artisan::call('clear-compiled');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return response()->json([
            'thanh_cong' => $success,
            'thong_bao' => $success 
                ? 'Đã regenerate autoloader thành công!' 
                : 'Không thể chạy composer dump-autoload',
            'chi_tiet' => [
                'composer_command' => $useComposerPhar ? 'php composer.phar dump-autoload' : 'composer dump-autoload',
                'return_code' => $returnCode,
                'output' => implode("\n", $output),
                'bootstrap_cache_cleared' => $filesCleared,
                'laravel_caches_cleared' => ['compiled', 'config', 'cache']
            ],
            'buoc_tiep_theo' => $success 
                ? 'Refresh trang (Ctrl+F5) và thử lại. Lỗi "Cannot declare class" nên đã biến mất.' 
                : 'Liên hệ hosting để họ chạy: composer dump-autoload',
            'huong_dan_hosting' => !$success ? [
                'subject' => 'Request to Run Composer Dump-Autoload',
                'body' => "Dear Support Team,\n\n" .
                    "I am experiencing a 'Cannot declare class' error in my Laravel application.\n\n" .
                    "Domain: " . request()->getHost() . "\n" .
                    "Issue: The composer autoloader needs to be regenerated\n\n" .
                    "Request: Please run the following command in my project root:\n" .
                    "cd " . base_path() . " && composer dump-autoload\n\n" .
                    "This will regenerate the autoloader class map and fix the class redeclaration error.\n\n" .
                    "Thank you!"
            ] : null
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'thanh_cong' => false,
            'loi' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'huong_dan' => 'Liên hệ hosting để họ chạy: composer dump-autoload'
        ]);
    }
});

// Public Traceability Routes (no auth required)
// Public Traceability Routes (no auth required, with rate limiting)
Route::middleware(['rate.limit.trace'])->group(function () {
    Route::get('/trace', [PublicTraceController::class, 'show'])->name('public.trace');
    Route::get('/trace/{tlc}', [PublicTraceController::class, 'show'])->name('public.trace.tlc');
    Route::get('/api/trace', [PublicTraceController::class, 'api'])->name('api.trace');
});

// API endpoint with stricter rate limiting
Route::middleware(['throttle:30,1'])->group(function () {
    // This was previously here, now moved to the rate.limit.trace middleware group above.
    // Route::get('/api/trace', [PublicTraceController::class, 'api'])->name('api.trace');
});

Route::get('/debug/check-password', function() {
    $email = request('email', 'admin@fsma204.com');
    $password = request('password', 'admin123');
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json([
            'loi' => 'Không tìm thấy user',
            'email_tim_kiem' => $email,
            'danh_sach_email_co_san' => \App\Models\User::pluck('email')->toArray()
        ]);
    }
    
    $hashCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    
    return response()->json([
        'email' => $user->email,
        'mat_khau_hash' => $user->password,
        'do_dai_hash' => strlen($user->password),
        'bat_dau_bang' => substr($user->password, 0, 10),
        'la_bcrypt' => str_starts_with($user->password, '$2y$'),
        'mat_khau_test' => $password,
        'do_dai_mat_khau_test' => strlen($password),
        'ket_qua_kiem_tra' => $hashCheck,
        'thong_bao' => $hashCheck 
            ? 'THÀNH CÔNG: Hash::check() trả về TRUE - Mật khẩu đúng!' 
            : 'THẤT BẠI: Hash::check() trả về FALSE - Mật khẩu sai hoặc hash bị lỗi!',
        'huong_dan' => $hashCheck 
            ? 'Nếu vẫn không login được, vấn đề nằm ở Session/Cookie. Khởi động lại PHP-FPM.' 
            : 'Hash không khớp. Chạy /debug/reset-admin-password để tạo hash mới.'
    ]);
});

Route::get('/debug/reset-admin-password', function() {
    $email = request('email', 'admin@fsma204.com');
    $newPassword = request('password', 'admin123');
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json([
            'loi' => 'Không tìm thấy user',
            'email_tim_kiem' => $email
        ]);
    }
    
    // Create new hash
    $newHash = \Illuminate\Support\Facades\Hash::make($newPassword);
    
    // Update directly with DB query to avoid any model interference
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $user->id)
        ->update(['password' => $newHash]);
    
    // Verify it was saved
    $user = \App\Models\User::find($user->id);
    $verification = \Illuminate\Support\Facades\Hash::check($newPassword, $user->password);
    
    return response()->json([
        'thanh_cong' => true,
        'thong_bao' => 'Đã reset mật khẩu thành công',
        'email' => $user->email,
        'mat_khau_moi' => $newPassword,
        'hash_bat_dau_bang' => substr($user->password, 0, 10),
        'xac_minh' => $verification ? 'PASS' : 'FAIL',
        'ket_qua' => $verification 
            ? 'THÀNH CÔNG: Mật khẩu đã được lưu và xác minh đúng!' 
            : 'CẢNH BÁO: Mật khẩu đã lưu nhưng không verify được!',
        'buoc_tiep_theo' => 'Thử login với email: ' . $user->email . ' và password: ' . $newPassword
    ]);
});

Route::get('/debug/auth-test', function() {
    $email = request('email', 'admin@fsma204.com');
    $password = request('password', 'admin123');
    
    $user = \App\Models\User::where('email', $email)->first();
    
    if (!$user) {
        return response()->json(['loi' => 'User không tồn tại']);
    }
    
    $hashCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    $authAttempt = \Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password]);
    
    return response()->json([
        'user_ton_tai' => true,
        'email' => $user->email,
        'hash_check' => $hashCheck,
        'auth_attempt' => $authAttempt,
        'auth_check' => \Illuminate\Support\Facades\Auth::check(),
        'ket_luan' => [
            'hash_check' => $hashCheck ? 'PASS' : 'FAIL',
            'auth_attempt' => $authAttempt ? 'PASS' : 'FAIL',
        ],
        'van_de' => !$hashCheck 
            ? 'Mật khẩu hash không khớp' 
            : (!$authAttempt ? 'Auth::attempt thất bại mặc dù hash đúng' : 'Không có vấn đề')
    ]);
});

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('api/master-data')->name('api.master-data.')->group(function () {
        Route::get('/products', [\App\Http\Controllers\Api\MasterDataController::class, 'getFTLProducts'])->name('products');
        Route::get('/products/{id}', [\App\Http\Controllers\Api\MasterDataController::class, 'getProduct'])->name('products.show');
        Route::get('/locations', [\App\Http\Controllers\Api\MasterDataController::class, 'getLocations'])->name('locations');
        Route::get('/locations/{id}', [\App\Http\Controllers\Api\MasterDataController::class, 'getLocation'])->name('locations.show');
        Route::get('/suppliers', [\App\Http\Controllers\Api\MasterDataController::class, 'getSuppliers'])->name('suppliers');
        Route::get('/partners/{id}', [\App\Http\Controllers\Api\MasterDataController::class, 'getPartner'])->name('partners.show');
        Route::get('/customers', [\App\Http\Controllers\Api\MasterDataController::class, 'getCustomers'])->name('customers');
    });
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/blocking', [NotificationController::class, 'getBlocking']);
    });
    
    // Pricing Routes
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
    Route::post('/pricing/upgrade', [PricingController::class, 'upgrade'])->name('pricing.upgrade');
    
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('partners', PartnerController::class);
    });
    
    // Document Management
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::post('/{document}/approve', [DocumentController::class, 'approve'])->name('approve');
        Route::post('/{document}/new-version', [DocumentController::class, 'newVersion'])->name('new-version');
    });
    
    // CTE Data Capture
    Route::prefix('cte')->name('cte.')->group(function () {
        Route::get('/receiving', [CTEController::class, 'receiving'])->name('receiving');
        Route::post('/receiving', [CTEController::class, 'storeReceiving']);
        
        Route::get('/transformation', [CTEController::class, 'transformation'])->name('transformation');
        Route::post('/transformation', [CTEController::class, 'storeTransformation']);
        
        Route::get('/shipping', [CTEController::class, 'shipping'])->name('shipping');
        Route::post('/shipping', [CTEController::class, 'storeShipping']);
        
        Route::get('/consumption-history/{traceRecord}', [CTEController::class, 'consumptionHistory'])->name('consumption-history');
        
        // VOID routes for each event type
        Route::post('/receiving/{event}/void', [CTEController::class, 'voidAndReentry'])->name('receiving.void');
        Route::post('/shipping/{event}/void', [CTEController::class, 'voidAndReentry'])->name('shipping.void');
        Route::post('/transformation/{event}/void', [CTEController::class, 'voidAndReentry'])->name('transformation.void');
        
        // RE-ENTRY routes for each event type
        Route::get('/receiving/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('receiving.reentry');
        Route::get('/shipping/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('shipping.reentry');
        Route::get('/transformation/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('transformation.reentry');
    });
    
    // Query & Reporting
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/traceability', [ReportController::class, 'traceability'])->name('traceability');
        Route::post('/traceability/query', [ReportController::class, 'queryTraceability'])->name('traceability.query');
        Route::get('/traceability/export', [ReportController::class, 'exportTraceability'])->name('traceability.export');
        Route::get('/traceability/export-pdf', [ReportController::class, 'exportTraceabilityPdf'])->name('traceability.export-pdf');
        Route::get('/traceability/analytics', [ReportController::class, 'analytics'])->name('traceability.analytics');
        
        Route::get('/audit-log', [AuditController::class, 'index'])->name('audit-log');
        Route::get('/audit-log/export', [AuditController::class, 'export'])->name('audit-log.export');
        Route::get('/audit-log/{id}', [AuditController::class, 'show']);
    });
    
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::post('/create', [App\Http\Controllers\CheckoutController::class, 'createCheckoutSession'])->name('create');
        Route::get('/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('success');
        Route::get('/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('cancel');
    });
    
    Route::prefix('vnpay')->name('vnpay.')->group(function () {
        Route::post('/create', [App\Http\Controllers\VNPayController::class, 'createPayment'])->name('create');
        Route::get('/return', [App\Http\Controllers\VNPayController::class, 'returnUrl'])->name('return');
    });
    
    // Admin Routes
    Route::middleware(['admin.or.manager'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('users/{user}/update-package', [UserController::class, 'updatePackage'])->name('users.update-package');
        
        // Leads Management Routes
        Route::prefix('leads')->name('leads.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LeadController::class, 'index'])->name('index');
            Route::get('/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'show'])->name('show');
            Route::get('/{lead}/edit', [\App\Http\Controllers\Admin\LeadController::class, 'edit'])->name('edit');
            Route::put('/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'update'])->name('update');
            Route::delete('/{lead}', [\App\Http\Controllers\Admin\LeadController::class, 'destroy'])->name('destroy');
            Route::get('/statistics', [\App\Http\Controllers\Admin\LeadController::class, 'statistics'])->name('statistics');
            Route::get('/export', [\App\Http\Controllers\Admin\LeadController::class, 'export'])->name('export');
        });
    });
    
    // Admin Routes (only for Admin users)
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('packages')->name('packages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PackageController::class, 'index'])->name('index');
            Route::get('/{package}/edit', [\App\Http\Controllers\Admin\PackageController::class, 'edit'])->name('edit');
            Route::put('/{package}', [\App\Http\Controllers\Admin\PackageController::class, 'update'])->name('update');
        });
        
        // The correct route is defined below in the e-signatures group using EnhancedESignatureController
        Route::get('/compliance-report', [ReportController::class, 'compliance'])->name('compliance');

        // Retention Policy Management Routes (Admin only)
        Route::prefix('retention')->name('retention.')->group(function () {
            Route::get('/', [RetentionPolicyController::class, 'index'])->name('index');
            Route::post('/store', [RetentionPolicyController::class, 'store'])->name('store');
            Route::get('/{policy}/edit', [RetentionPolicyController::class, 'edit'])->name('edit');
            Route::put('/{policy}', [RetentionPolicyController::class, 'update'])->name('update');
            Route::post('/{policy}/execute', [RetentionPolicyController::class, 'execute'])->name('execute');
            Route::get('/logs', [RetentionPolicyController::class, 'logs'])->name('logs');
        });
        
        Route::prefix('archival')->name('archival.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ArchivalController::class, 'index'])->name('index');
            Route::post('/execute', [\App\Http\Controllers\ArchivalController::class, 'execute'])->name('execute');
            Route::get('/logs', [\App\Http\Controllers\ArchivalController::class, 'logs'])->name('logs');
            Route::get('/view/{dataType}', [\App\Http\Controllers\ArchivalController::class, 'viewArchived'])->name('view');
        });

        // Enhanced E-Signature Routes
        Route::prefix('e-signatures')
            ->name('e-signatures.')
            ->middleware('rate.limit.signatures')
            ->group(function () {
                Route::get('/', [EnhancedESignatureController::class, 'index'])->name('index');
                Route::get('/audit-trail', [EnhancedESignatureController::class, 'auditTrail'])->name('audit-trail');
                Route::get('/performance-dashboard', [EnhancedESignatureController::class, 'performanceDashboard'])->name('performance-dashboard');
                
                // JSON endpoint for AJAX dashboard
                Route::get('/performance-metrics', [EnhancedESignatureController::class, 'performanceMetrics'])
                    ->name('performance-metrics');
                
                Route::get('/{signature}', [EnhancedESignatureController::class, 'show'])->name('show');
                Route::post('/sign', [EnhancedESignatureController::class, 'sign'])->name('sign');
                Route::post('/verify', [EnhancedESignatureController::class, 'verify'])->name('verify');
                Route::post('/revoke', [EnhancedESignatureController::class, 'revoke'])->name('revoke');
            });
        
        // Admin Record Type Routes
        Route::prefix('e-signatures/record-types')
            ->name('e-signatures.record-types.')
            ->group(function () {
                Route::get('/', [SignatureRecordTypeController::class, 'index'])->name('index');
                Route::get('/{id}', [SignatureRecordTypeController::class, 'show'])->name('show');
                Route::post('/', [SignatureRecordTypeController::class, 'store'])->name('store');
                Route::put('/{id}', [SignatureRecordTypeController::class, 'update'])->name('update');
                Route::post('/{id}/toggle', [SignatureRecordTypeController::class, 'toggle'])->name('toggle');
            });
    });

    // Two-Factor Authentication Routes
    Route::prefix('two-fa')->name('two-fa.')->group(function () {
        Route::get('/setup', [TwoFactorAuthController::class, 'showSetup'])->name('setup');
        Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->name('enable');
        Route::post('/disable', [TwoFactorAuthController::class, 'disable'])->name('disable');
        Route::get('/verify', [TwoFactorAuthController::class, 'showVerify'])->name('verify');
        Route::post('/verify', [TwoFactorAuthController::class, 'verify']);
    });

    // Digital Certificate Routes
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [DigitalCertificateController::class, 'index'])->name('index');
        Route::post('/generate', [DigitalCertificateController::class, 'generate'])->name('generate');
        Route::get('/download', [DigitalCertificateController::class, 'download'])->name('download');
        Route::get('/{id}/download-public-key', [DigitalCertificateController::class, 'downloadPublicKey'])->name('download-public-key');
        Route::post('/revoke', [DigitalCertificateController::class, 'revoke'])->name('revoke');
    });

// ============================
// Enhanced E-Signature Routes
// ============================
// Moved inside admin middleware group above

// ============================
// Admin Record Type Routes
// ============================
// Moved inside admin middleware group above
});

Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->middleware('throttle:60,1')
    ->name('stripe.webhook');

Route::post('/vnpay/ipn', [App\Http\Controllers\VNPayController::class, 'ipn'])
    ->middleware('throttle:60,1')
    ->name('vnpay.ipn');

Route::prefix('qa-test')->group(function() {
    
    // Test 1: Database Connection & User Table Structure
    Route::get('/1-database-check', function() {
        try {
            $tableExists = \Illuminate\Support\Facades\Schema::hasTable('users');
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('users');
            $passwordColumn = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM users WHERE Field = 'password'");
            
            $users = \Illuminate\Support\Facades\DB::table('users')->select('id', 'email', 'password')->get();
            
            return response()->json([
                'test' => 'TEST 1: Kiểm tra Database & Cấu trúc bảng',
                'ket_qua' => 'PASS',
                'bang_users_ton_tai' => $tableExists,
                'cac_cot' => $columns,
                'thong_tin_cot_password' => $passwordColumn,
                'so_luong_users' => $users->count(),
                'danh_sach_users' => $users->map(function($u) {
                    return [
                        'id' => $u->id,
                        'email' => $u->email,
                        'password_length' => strlen($u->password),
                        'password_prefix' => substr($u->password, 0, 10),
                        'is_bcrypt' => str_starts_with($u->password, '$2y$'),
                    ];
                }),
                'ket_luan' => 'Database kết nối OK, bảng users tồn tại với ' . $users->count() . ' users'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'test' => 'TEST 1: Kiểm tra Database',
                'ket_qua' => 'FAIL',
                'loi' => $e->getMessage()
            ]);
        }
    });
    
    // Test 2: Password Hash Validation
    Route::get('/2-hash-validation', function() {
        $email = request('email', 'admin@fsma204.com');
        $password = request('password', 'admin123');
        
        $user = \Illuminate\Support\Facades\DB::table('users')->where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'test' => 'TEST 2: Kiểm tra Password Hash',
                'ket_qua' => 'FAIL',
                'loi' => 'User không tồn tại',
                'email_tim_kiem' => $email,
                'goi_y' => 'Thử với email khác hoặc chạy seeder'
            ]);
        }
        
        // Test hash với nhiều cách khác nhau
        $hashInfo = password_get_info($user->password);
        $manualCheck = password_verify($password, $user->password);
        $laravelCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        
        // Test với password có khoảng trắng
        $trimmedPassword = trim($password);
        $trimmedCheck = \Illuminate\Support\Facades\Hash::check($trimmedPassword, $user->password);
        
        return response()->json([
            'test' => 'TEST 2: Kiểm tra Password Hash',
            'email' => $user->email,
            'password_test' => $password,
            'password_length' => strlen($password),
            'password_trimmed' => $trimmedPassword,
            'hash_info' => [
                'algo' => $hashInfo['algo'],
                'algo_name' => $hashInfo['algoName'],
                'options' => $hashInfo['options'],
            ],
            'hash_details' => [
                'full_hash' => $user->password,
                'length' => strlen($user->password),
                'prefix' => substr($user->password, 0, 10),
                'is_bcrypt' => str_starts_with($user->password, '$2y$'),
            ],
            'kiem_tra_password' => [
                'php_password_verify' => $manualCheck ? 'PASS' : 'FAIL',
                'laravel_hash_check' => $laravelCheck ? 'PASS' : 'FAIL',
                'trimmed_password_check' => $trimmedCheck ? 'PASS' : 'FAIL',
            ],
            'ket_qua' => ($manualCheck && $laravelCheck) ? 'PASS' : 'FAIL',
            'ket_luan' => ($manualCheck && $laravelCheck) 
                ? 'Password hash hợp lệ và khớp với password test' 
                : 'Password KHÔNG KHỚP với hash. Cần reset password.',
            'huong_dan' => !$laravelCheck 
                ? 'Chạy: /qa-test/3-reset-password?email=' . $email . '&password=' . $password 
                : 'Hash OK. Tiếp tục test tiếp theo.'
        ]);
    });
    
    // Test 3: Reset Password với verification đầy đủ
    Route::get('/3-reset-password', function() {
        $email = request('email', 'admin@fsma204.com');
        $password = request('password', 'admin123');
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'test' => 'TEST 3: Reset Password',
                'ket_qua' => 'FAIL',
                'loi' => 'User không tồn tại'
            ]);
        }
        
        // Lưu hash cũ để so sánh
        $oldHash = $user->password;
        
        // Tạo hash mới
        $newHash = \Illuminate\Support\Facades\Hash::make($password);
        
        // Update trực tiếp vào DB
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => $newHash,
                'updated_at' => now()
            ]);
        
        // Đọc lại từ DB
        $updatedUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->first();
        
        // Verify hash mới
        $verification = \Illuminate\Support\Facades\Hash::check($password, $updatedUser->password);
        
        return response()->json([
            'test' => 'TEST 3: Reset Password',
            'ket_qua' => $verification ? 'PASS' : 'FAIL',
            'email' => $email,
            'password_moi' => $password,
            'hash_cu' => [
                'value' => substr($oldHash, 0, 20) . '...',
                'length' => strlen($oldHash)
            ],
            'hash_moi' => [
                'value' => substr($newHash, 0, 20) . '...',
                'length' => strlen($newHash)
            ],
            'hash_trong_db' => [
                'value' => substr($updatedUser->password, 0, 20) . '...',
                'length' => strlen($updatedUser->password),
                'khop_voi_hash_moi' => $updatedUser->password === $newHash
            ],
            'xac_minh' => $verification ? 'PASS' : 'FAIL',
            'ket_qua' => $verification 
                ? 'THÀNH CÔNG: Password đã được reset và xác minh thành công!' 
                : 'CẢNH BÁO: Password đã lưu nhưng không verify được!',
            'buoc_tiep_theo' => 'Chạy: /qa-test/4-auth-attempt?email=' . $email . '&password=' . $password
        ]);
    });
    
    // Test 4: Auth::attempt() Testing
    Route::get('/4-auth-attempt', function() {
        $email = request('email', 'admin@fsma204.com');
        $password = request('password', 'admin123');
        
        // Logout trước
        \Illuminate\Support\Facades\Auth::logout();
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'test' => 'TEST 4: Auth::attempt()',
                'ket_qua' => 'FAIL',
                'loi' => 'User không tồn tại'
            ]);
        }
        
        // Test manual hash check trước
        $hashCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        
        // Test Auth::attempt()
        $attemptResult = \Illuminate\Support\Facades\Auth::attempt([
            'email' => $email,
            'password' => $password
        ]);
        
        $isAuthenticated = \Illuminate\Support\Facades\Auth::check();
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        
        return response()->json([
            'test' => 'TEST 4: Auth::attempt()',
            'credentials' => [
                'email' => $email,
                'password' => $password
            ],
            'user_ton_tai' => true,
            'hash_check_manual' => $hashCheck ? 'PASS' : 'FAIL',
            'auth_attempt_result' => $attemptResult ? 'PASS' : 'FAIL',
            'auth_check' => $isAuthenticated ? 'PASS' : 'FAIL',
            'current_user' => $currentUser ? $currentUser->email : null,
            'ket_qua' => ($hashCheck && $attemptResult && $isAuthenticated) ? 'PASS' : 'FAIL',
            'phan_tich' => [
                'hash_check' => $hashCheck ? 'OK' : 'FAIL - Password không khớp hash',
                'auth_attempt' => $attemptResult ? 'OK' : 'FAIL - Auth::attempt() thất bại',
                'session' => $isAuthenticated ? 'OK' : 'FAIL - Session không được tạo',
            ],
            'ket_luan' => ($hashCheck && $attemptResult && $isAuthenticated)
                ? 'Authentication hoạt động HOÀN HẢO! Có thể login bình thường tại /login'
                : 'Có vấn đề với authentication flow. Xem phân tích chi tiết ở trên.'
        ]);
    });
    
    // Test 5: Full Login Flow Simulation
    Route::get('/5-full-login-test', function() {
        $email = request('email', 'admin@fsma204.com');
        $password = request('password', 'admin123');
        
        $steps = [];
        
        // Step 1: Check user exists
        $user = \App\Models\User::where('email', $email)->first();
        $steps['step_1_user_lookup'] = $user ? 'PASS' : 'FAIL';
        
        if (!$user) {
            return response()->json([
                'test' => 'TEST 5: Full Login Flow',
                'ket_qua' => 'FAIL',
                'steps' => $steps,
                'loi' => 'User không tồn tại'
            ]);
        }
        
        // Step 2: Verify password hash
        $hashCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);
        $steps['step_2_hash_check'] = $hashCheck ? 'PASS' : 'FAIL';
        
        // Step 3: Logout current session
        \Illuminate\Support\Facades\Auth::logout();
        $steps['step_3_logout'] = 'DONE';
        
        // Step 4: Attempt login
        $loginSuccess = \Illuminate\Support\Facades\Auth::attempt([
            'email' => $email,
            'password' => $password
        ], true); // remember = true
        $steps['step_4_auth_attempt'] = $loginSuccess ? 'PASS' : 'FAIL';
        
        // Step 5: Check session
        $isAuthenticated = \Illuminate\Support\Facades\Auth::check();
        $steps['step_5_session_check'] = $isAuthenticated ? 'PASS' : 'FAIL';
        
        // Step 6: Get authenticated user
        $authUser = \Illuminate\Support\Facades\Auth::user();
        $steps['step_6_get_user'] = $authUser ? 'PASS' : 'FAIL';
        
        $allPassed = $hashCheck && $loginSuccess && $isAuthenticated && $authUser;
        
        return response()->json([
            'test' => 'TEST 5: Full Login Flow Simulation',
            'ket_qua' => $allPassed ? 'PASS - HỆ THỐNG HOẠT ĐỘNG BÌNH THƯỜNG' : 'FAIL - CÓ VẤN ĐỀ',
            'credentials' => [
                'email' => $email,
                'password' => $password
            ],
            'chi_tiet_tung_buoc' => $steps,
            'authenticated_user' => $authUser ? [
                'id' => $authUser->id,
                'email' => $authUser->email,
                'name' => $authUser->name,
                'role' => $authUser->role
            ] : null,
            'ket_luan' => $allPassed 
                ? '✅ TẤT CẢ TESTS PASS! Hệ thống authentication hoạt động hoàn hảo. Bạn có thể login bình thường tại /login'
                : '❌ CÓ VẤN ĐỀ! Xem chi tiết từng bước ở trên để xác định vấn đề.',
            'huong_dan_khac_phuc' => !$allPassed ? [
                'neu_step_2_fail' => 'Password hash không khớp. Chạy /qa-test/3-reset-password',
                'neu_step_4_fail' => 'Auth::attempt() thất bại. Kiểm tra User model và AuthController',
                'neu_step_5_fail' => 'Session không hoạt động. Kiểm tra SESSION_DRIVER trong .env và quyền thư mục storage/framework/sessions',
                'neu_step_6_fail' => 'Không lấy được user từ session. Restart PHP-FPM/Apache'
            ] : null
        ]);
    });
    
    // Test 6: User Isolation Check - Kiểm tra session và data isolation
    Route::get('/6-user-isolation-check', function() {
        $results = [];
        
        // Check current authenticated user
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $results['current_user'] = $currentUser ? [
            'id' => $currentUser->id,
            'email' => $currentUser->email,
            'username' => $currentUser->username,
            'role' => $currentUser->role
        ] : null;
        
        // Check session data
        $results['session_data'] = [
            'session_id' => session()->getId(),
            'all_session_keys' => array_keys(session()->all()),
            'auth_session_keys' => array_filter(array_keys(session()->all()), function($key) {
                return str_contains($key, 'login') || str_contains($key, 'auth') || str_contains($key, 'user');
            })
        ];
        
        // Check MySQL session variables
        try {
            $mysqlVars = \Illuminate\Support\Facades\DB::select('SELECT @current_user_id as user_id, @client_ip as ip, @user_agent as agent');
            $results['mysql_session_vars'] = $mysqlVars[0] ?? null;
        } catch (\Exception $e) {
            $results['mysql_session_vars'] = 'Error: ' . $e->getMessage();
        }
        
        // Check recent audit logs for this user
        if ($currentUser) {
            $recentAudits = \Illuminate\Support\Facades\DB::table('audit_logs')
                ->where('user_id', $currentUser->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'action', 'table_name', 'created_at']);
            
            $results['recent_audit_logs'] = $recentAudits;
        }
        
        // Check if there are any cached user data
        $results['cache_check'] = [
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];
        
        return response()->json([
            'test' => 'TEST 6: User Isolation Check',
            'mo_ta' => 'Kiểm tra xem user mới có bị lẫn data từ user cũ không',
            'ket_qua' => $results,
            'phan_tich' => [
                'user_authenticated' => $currentUser ? 'Có user đang login' : 'Không có user nào login',
                'session_clean' => count($results['session_data']['auth_session_keys']) <= 2 ? 'Session sạch' : 'Session có nhiều keys',
                'mysql_vars_match' => ($results['mysql_session_vars']->user_id ?? 0) == ($currentUser->id ?? 0) ? 'MySQL vars khớp' : 'MySQL vars KHÔNG khớp',
            ],
            'ket_luan' => 'Xem chi tiết ở trên để xác định có data leakage không',
            'huong_dan' => [
                'test_isolation' => 'Logout, login với user khác, rồi chạy lại test này',
                'clear_cache' => 'php artisan cache:clear && php artisan config:clear',
                'clear_sessions' => 'Xóa files trong storage/framework/sessions/'
            ]
        ]);
    })->name('qa-test.user-isolation');
    
    // Test 7: OPcache Diagnostic - Check if OPcache is causing class redeclaration
    Route::get('/7-opcache-diagnostic', function() {
        $results = [];
        
        // Check if OPcache is enabled
        $results['opcache_enabled'] = function_exists('opcache_get_status') && opcache_get_status();
        
        if ($results['opcache_enabled']) {
            $status = opcache_get_status();
            $results['opcache_status'] = [
                'enabled' => $status['opcache_enabled'] ?? false,
                'cache_full' => $status['cache_full'] ?? false,
                'restart_pending' => $status['restart_pending'] ?? false,
                'restart_in_progress' => $status['restart_in_progress'] ?? false,
                'memory_usage' => [
                    'used_memory' => round(($status['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2) . ' MB',
                    'free_memory' => round(($status['memory_usage']['free_memory'] ?? 0) / 1024 / 1024, 2) . ' MB',
                    'wasted_memory' => round(($status['memory_usage']['wasted_memory'] ?? 0) / 1024 / 1024, 2) . ' MB',
                ],
                'statistics' => [
                    'num_cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
                    'hits' => $status['opcache_statistics']['hits'] ?? 0,
                    'misses' => $status['opcache_statistics']['misses'] ?? 0,
                    'blacklist_misses' => $status['opcache_statistics']['blacklist_misses'] ?? 0,
                ]
            ];
            
            // Check if User.php is cached
            $userFilePath = app_path('Models/User.php');
            $results['user_file'] = [
                'path' => $userFilePath,
                'exists' => file_exists($userFilePath),
                'size' => file_exists($userFilePath) ? filesize($userFilePath) : 0,
                'modified' => file_exists($userFilePath) ? date('Y-m-d H:i:s', filemtime($userFilePath)) : null,
            ];
            
            // Try to get cached scripts
            if (function_exists('opcache_get_status')) {
                $scripts = $status['scripts'] ?? [];
                $userCached = isset($scripts[$userFilePath]);
                $results['user_file']['is_cached'] = $userCached;
                
                if ($userCached) {
                    $results['user_file']['cache_info'] = [
                        'hits' => $scripts[$userFilePath]['hits'] ?? 0,
                        'memory_consumption' => $scripts[$userFilePath]['memory_consumption'] ?? 0,
                        'last_used_timestamp' => date('Y-m-d H:i:s', $scripts[$userFilePath]['last_used_timestamp'] ?? 0),
                    ];
                }
            }
        } else {
            $results['opcache_status'] = 'OPcache is not enabled or not available';
        }
        
        // Try to reset OPcache
        $resetAttempt = false;
        $resetMessage = '';
        
        if (function_exists('opcache_reset')) {
            try {
                $resetAttempt = opcache_reset();
                $resetMessage = $resetAttempt ? 'OPcache reset thành công' : 'OPcache reset thất bại';
            } catch (\Exception $e) {
                $resetMessage = 'Lỗi khi reset OPcache: ' . $e->getMessage();
            }
        } else {
            $resetMessage = 'Function opcache_reset() không khả dụng';
        }
        
        $results['reset_attempt'] = [
            'attempted' => true,
            'success' => $resetAttempt,
            'message' => $resetMessage
        ];
        
        // Check PHP version and configuration
        $results['php_info'] = [
            'version' => PHP_VERSION,
            'opcache.enable' => ini_get('opcache.enable'),
            'opcache.enable_cli' => ini_get('opcache.enable_cli'),
            'opcache.revalidate_freq' => ini_get('opcache.revalidate_freq'),
            'opcache.validate_timestamps' => ini_get('opcache.validate_timestamps'),
        ];
        
        return response()->json([
            'test' => 'TEST 7: OPcache Diagnostic',
            'mo_ta' => 'Kiểm tra xem OPcache có đang cache code cũ không',
            'ket_qua' => $results,
            'phan_tich' => [
                'opcache_enabled' => $results['opcache_enabled'] ? '⚠️ OPcache ĐANG BẬT - Có thể cache code cũ' : '✅ OPcache TẮT - Không có vấn đề cache',
                'user_file_cached' => ($results['user_file']['is_cached'] ?? false) ? '⚠️ User.php ĐANG ĐƯỢC CACHE' : '✅ User.php không trong cache',
                'reset_success' => $resetAttempt ? '✅ Đã reset OPcache thành công' : '❌ Không thể reset OPcache',
            ],
            'ket_luan' => $results['opcache_enabled'] 
                ? 'OPcache đang bật và có thể đang cache phiên bản code cũ. ' . $resetMessage
                : 'OPcache không phải nguyên nhân. Kiểm tra syntax error trong User.php',
            'huong_dan_khac_phuc' => [
                'buoc_1' => 'Truy cập /clear-cache để xóa tất cả cache',
                'buoc_2' => 'Nếu vẫn lỗi, gửi tin nhắn cho hosting (xem bên dưới)',
                'buoc_3' => 'Sau khi hosting reset, refresh browser (Ctrl+F5)',
            ],
            'tin_nhan_gui_hosting' => [
                'subject' => 'Request to Clear PHP OPcache - Class Redeclaration Error',
                'body' => "Dear Support Team,\n\n" .
                    "I am experiencing a 'Cannot declare class App\\Models\\User, because the name is already in use' error.\n\n" .
                    "Domain: " . request()->getHost() . "\n" .
                    "PHP Version: " . PHP_VERSION . "\n" .
                    "Issue: PHP OPcache is caching an old version of app/Models/User.php\n\n" .
                    "Request: Please clear PHP OPcache and restart PHP-FPM service for my account.\n\n" .
                    "Commands needed:\n" .
                    "1. Clear OPcache: opcache_reset() or restart PHP-FPM\n" .
                    "2. Verify: Check if opcache.revalidate_freq is set to 0 for development\n\n" .
                    "Thank you!"
            ]
        ]);
    })->name('qa-test.opcache-diagnostic');
    
    // Test 8: Deep Class Redeclaration Diagnostic
    Route::get('/8-class-redeclaration-diagnostic', function() {
        $results = [];
        
        // 1. Check if User class is already declared
        $results['class_exists'] = class_exists(\App\Models\User::class, false);
        
        // 2. Find all PHP files that might be loading User.php
        $projectRoot = base_path();
        $suspiciousFiles = [];
        
        // Search for require/include statements
        $searchPatterns = [
            'require.*User\.php',
            'require_once.*User\.php',
            'include.*User\.php',
            'include_once.*User\.php',
        ];
        
        foreach ($searchPatterns as $pattern) {
            $command = "cd $projectRoot && grep -r '$pattern' --include='*.php' . 2>/dev/null || true";
            exec($command, $output);
            if (!empty($output)) {
                $suspiciousFiles[$pattern] = $output;
            }
        }
        
        $results['suspicious_files'] = $suspiciousFiles;
        
        // 3. Check composer autoloader
        $composerAutoload = base_path('vendor/autoload.php');
        $results['composer_autoload'] = [
            'exists' => file_exists($composerAutoload),
            'modified' => file_exists($composerAutoload) ? date('Y-m-d H:i:s', filemtime($composerAutoload)) : null,
            'size' => file_exists($composerAutoload) ? filesize($composerAutoload) : 0,
        ];
        
        // Check composer class map
        $classMapFile = base_path('vendor/composer/autoload_classmap.php');
        if (file_exists($classMapFile)) {
            $classMap = require $classMapFile;
            $userClassInMap = isset($classMap['App\\Models\\User']);
            $results['composer_classmap'] = [
                'exists' => true,
                'total_classes' => count($classMap),
                'user_class_registered' => $userClassInMap,
                'user_class_path' => $userClassInMap ? $classMap['App\\Models\\User'] : null,
            ];
        } else {
            $results['composer_classmap'] = ['exists' => false];
        }
        
        // Check PSR-4 autoload
        $psr4File = base_path('vendor/composer/autoload_psr4.php');
        if (file_exists($psr4File)) {
            $psr4 = require $psr4File;
            $results['psr4_autoload'] = [
                'exists' => true,
                'app_namespace' => isset($psr4['App\\']) ? $psr4['App\\'] : null,
            ];
        }
        
        // Check bootstrap cache files
        $bootstrapCache = base_path('bootstrap/cache');
        $cacheFiles = [];
        
        $filesToCheck = ['packages.php', 'services.php', 'config.php', 'routes-v7.php', 'compiled.php'];
        foreach ($filesToCheck as $file) {
            $filePath = $bootstrapCache . '/' . $file;
            if (file_exists($filePath)) {
                $cacheFiles[$file] = [
                    'exists' => true,
                    'size' => filesize($filePath),
                    'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                ];
            }
        }
        
        $results['bootstrap_cache_files'] = $cacheFiles;
        
        // Check User.php file itself
        $userFile = app_path('Models/User.php');
        $results['user_file'] = [
            'path' => $userFile,
            'exists' => file_exists($userFile),
            'size' => file_exists($userFile) ? filesize($userFile) : 0,
            'modified' => file_exists($userFile) ? date('Y-m-d H:i:s', filemtime($userFile)) : null,
            'readable' => file_exists($userFile) ? is_readable($userFile) : false,
            'writable' => file_exists($userFile) ? is_writable($userFile) : false,
        ];
        
        // Try to read and parse User.php
        if (file_exists($userFile)) {
            $content = file_get_contents($userFile);
            $results['user_file']['content_length'] = strlen($content);
            $results['user_file']['line_count'] = substr_count($content, "\n");
            
            // Count braces
            $openBraces = substr_count($content, '{');
            $closeBraces = substr_count($content, '}');
            $results['user_file']['syntax_check'] = [
                'open_braces' => $openBraces,
                'close_braces' => $closeBraces,
                'balanced' => $openBraces === $closeBraces,
            ];
            
            // Check for class declaration
            preg_match('/class\s+User\s+extends/', $content, $matches);
            $results['user_file']['class_declaration_found'] = !empty($matches);
            
            // Check for duplicate class declarations
            $classCount = substr_count($content, 'class User');
            $results['user_file']['class_declaration_count'] = $classCount;
        }
        
        // Check if there are other User.php files
        $command = "find $projectRoot -name 'User.php' -type f 2>/dev/null || true";
        exec($command, $allUserFiles);
        $results['all_user_files'] = $allUserFiles;
        
        // Detect the actual error
        $errorDetected = false;
        $errorReason = '';
        
        if (!empty($suspiciousFiles)) {
            $errorDetected = true;
            $errorReason = 'Tìm thấy files đang require/include User.php thủ công';
        } elseif (count($allUserFiles) > 1) {
            $errorDetected = true;
            $errorReason = 'Có nhiều hơn 1 file User.php trong project';
        } elseif (!($results['user_file']['syntax_check']['balanced'] ?? true)) {
            $errorDetected = true;
            $errorReason = 'User.php có syntax error (braces không cân bằng)';
        } elseif (($results['user_file']['class_declaration_count'] ?? 0) > 1) {
            $errorDetected = true;
            $errorReason = 'User.php có nhiều hơn 1 class declaration';
        } elseif (!empty($cacheFiles)) {
            $errorDetected = true;
            $errorReason = 'Bootstrap cache files có thể chứa code cũ';
        }
        
        return response()->json([
            'test' => 'TEST 8: Deep Class Redeclaration Diagnostic',
            'mo_ta' => 'Phân tích sâu để tìm nguyên nhân "Cannot declare class App\\Models\\User"',
            'ket_qua' => $results,
            'phan_tich' => [
                'class_already_loaded' => $results['class_exists'] ? '⚠️ Class đã được load trước đó' : '✅ Class chưa được load',
                'suspicious_files' => !empty($suspiciousFiles) ? '⚠️ Tìm thấy ' . count($suspiciousFiles) . ' pattern(s) đáng ngờ' : '✅ Không có file nào require/include User.php',
                'multiple_user_files' => count($allUserFiles) > 1 ? '⚠️ Có ' . count($allUserFiles) . ' file User.php' : '✅ Chỉ có 1 file User.php',
                'syntax_valid' => ($results['user_file']['syntax_check']['balanced'] ?? false) ? '✅ Syntax hợp lệ (braces cân bằng)' : '❌ Syntax error (braces không cân bằng)',
                'bootstrap_cache' => !empty($cacheFiles) ? '⚠️ Có ' . count($cacheFiles) . ' cache files' : '✅ Không có cache files',
            ],
            'error_detected' => $errorDetected,
            'error_reason' => $errorReason,
            'ket_luan' => $errorDetected 
                ? '🔴 TÌM THẤY VẤN ĐỀ: ' . $errorReason
                : '🟢 KHÔNG TÌM THẤY VẤN ĐỀ RÕ RÀNG - Có thể là vấn đề về server/hosting',
            'huong_dan_khac_phuc' => [
                'buoc_1' => 'Xóa tất cả bootstrap cache: truy cập /clear-cache',
                'buoc_2' => 'Nếu vẫn lỗi, xóa thủ công các file trong bootstrap/cache/',
                'buoc_3' => 'Restart PHP-FPM/Apache (liên hệ hosting)',
                'buoc_4' => 'Nếu vẫn không được, gửi tin nhắn cho hosting (xem bên dưới)',
            ],
            'tin_nhan_gui_hosting' => [
                'subject' => 'Urgent: Cannot Declare Class Error - Need Server-Side Fix',
                'body' => "Dear Support Team,\n\n" .
                    "I am experiencing a 'Cannot declare class App\\Models\\User, because the name is already in use' error that cannot be resolved through application-level cache clearing.\n\n" .
                    "Domain: " . request()->getHost() . "\n" .
                    "PHP Version: " . PHP_VERSION . "\n" .
                    "Framework: Laravel 11\n\n" .
                    "Diagnostic Results:\n" .
                    "- OPcache Status: " . (function_exists('opcache_get_status') ? 'Enabled' : 'Disabled') . "\n" .
                    "- Bootstrap Cache: " . count($cacheFiles) . " files found\n" .
                    "- Composer Autoloader: " . ($results['composer_autoload']['exists'] ? 'Exists' : 'Missing') . "\n\n" .
                    "Request: Please perform the following actions:\n" .
                    "1. Clear PHP OPcache completely\n" .
                    "2. Restart PHP-FPM service\n" .
                    "3. Run: cd " . base_path() . " && composer dump-autoload\n" .
                    "4. Clear all cache: cd " . base_path() . " && php artisan optimize:clear\n" .
                    "5. Verify file permissions on app/Models/User.php\n\n" .
                    "This error suggests that the PHP process is caching an old version of the class file or the autoloader needs to be regenerated at the server level.\n\n" .
                    "Thank you for your urgent assistance!"
            ]
        ]);
    })->name('qa-test.class-diagnostic');
    
    // Test Summary - Chạy tất cả tests
    Route::get('/run-all', function() {
        return response()->json([
            'huong_dan' => 'QA Test Suite - Chạy từng test theo thứ tự',
            'tests' => [
                '1' => [
                    'ten' => 'Database Check',
                    'url' => url('/qa-test/1-database-check'),
                    'mo_ta' => 'Kiểm tra kết nối DB và cấu trúc bảng users'
                ],
                '2' => [
                    'ten' => 'Password Hash Validation',
                    'url' => url('/qa-test/2-hash-validation?email=admin@fsma204.com&password=admin123'),
                    'mo_ta' => 'Kiểm tra password hash có hợp lệ và khớp không'
                ],
                '3' => [
                    'ten' => 'Reset Password',
                    'url' => url('/qa-test/3-reset-password?email=admin@fsma204.com&password=admin123'),
                    'mo_ta' => 'Reset password và verify lại'
                ],
                '4' => [
                    'ten' => 'Auth::attempt() Test',
                    'url' => url('/qa-test/4-auth-attempt?email=admin@fsma204.com&password=admin123'),
                    'mo_ta' => 'Test Laravel Auth::attempt() function'
                ],
                '5' => [
                    'ten' => 'Full Login Flow Simulation',
                    'url' => url('/qa-test/5-full-login-test?email=admin@fsma204.com&password=admin123'),
                    'mo_ta' => 'Simulate toàn bộ login flow từ đầu đến cuối'
                ],
                '6' => [
                    'ten' => 'User Isolation Check',
                    'url' => url('/qa-test/6-user-isolation-check'),
                    'mo_ta' => 'Kiểm tra xem user mới có bị lẫn data từ user cũ không'
                ],
                '7' => [
                    'ten' => 'OPcache Diagnostic',
                    'url' => url('/qa-test/7-opcache-diagnostic'),
                    'mo_ta' => 'Kiểm tra xem OPcache có đang cache phiên bản code cũ không'
                ],
                '8' => [
                    'ten' => 'Deep Class Redeclaration Diagnostic',
                    'url' => url('/qa-test/8-class-redeclaration-diagnostic'),
                    'mo_ta' => 'Phân tích sâu để tìm nguyên nhân "Cannot declare class" error'
                ]
            ],
            'khuyen_nghị' => 'Nếu gặp lỗi "Cannot declare class", chạy test 8 để có diagnostic đầy đủ.'
        ]);
    });

    Route::get('/nuclear-reset', function() {
        $results = [];
        $errors = [];
        
        try {
            // Step 1: Delete all bootstrap cache files
            $bootstrapCache = base_path('bootstrap/cache');
            $cacheFiles = ['packages.php', 'services.php', 'config.php', 'routes-v7.php', 'compiled.php', 'routes.php'];
            $deletedFiles = [];
            
            foreach ($cacheFiles as $file) {
                $filePath = $bootstrapCache . '/' . $file;
                if (file_exists($filePath)) {
                    if (unlink($filePath)) {
                        $deletedFiles[] = $file;
                    } else {
                        $errors[] = "Không thể xóa $file";
                    }
                }
            }
            
            $results['step_1_bootstrap_cache'] = [
                'status' => 'DONE',
                'deleted_files' => $deletedFiles,
                'count' => count($deletedFiles)
            ];
            
            // Step 2: Delete composer autoloader cache files
            $composerCache = base_path('vendor/composer');
            $composerFiles = ['autoload_classmap.php', 'autoload_static.php'];
            $deletedComposerFiles = [];
            
            foreach ($composerFiles as $file) {
                $filePath = $composerCache . '/' . $file;
                if (file_exists($filePath)) {
                    // Backup first
                    $backupPath = $filePath . '.backup.' . time();
                    if (copy($filePath, $backupPath)) {
                        if (unlink($filePath)) {
                            $deletedComposerFiles[] = $file;
                        }
                    }
                }
            }
            
            $results['step_2_composer_cache'] = [
                'status' => 'DONE',
                'deleted_files' => $deletedComposerFiles,
                'count' => count($deletedComposerFiles)
            ];
            
            // Step 3: Clear all Laravel caches
            try {
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('clear-compiled');
                $results['step_3_laravel_caches'] = 'CLEARED';
            } catch (\Exception $e) {
                $errors[] = 'Laravel cache clear error: ' . $e->getMessage();
                $results['step_3_laravel_caches'] = 'PARTIAL';
            }
            
            // Step 4: Clear file cache manually
            $cachePath = storage_path('framework/cache/data');
            $clearedCount = 0;
            if (is_dir($cachePath)) {
                $files = glob($cachePath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $clearedCount++;
                    }
                }
            }
            $results['step_4_file_cache'] = [
                'status' => 'DONE',
                'files_cleared' => $clearedCount
            ];
            
            // Step 5: Clear OPcache if available
            if (function_exists('opcache_reset')) {
                try {
                    $opcacheReset = opcache_reset();
                    $results['step_5_opcache'] = $opcacheReset ? 'RESET' : 'FAILED';
                } catch (\Exception $e) {
                    $results['step_5_opcache'] = 'ERROR: ' . $e->getMessage();
                }
            } else {
                $results['step_5_opcache'] = 'NOT_AVAILABLE';
            }
            
            // Step 6: Try to regenerate composer autoloader
            $composerPath = base_path('composer.phar');
            $useComposerPhar = file_exists($composerPath);
            
            $output = [];
            $returnCode = 0;
            
            if ($useComposerPhar) {
                exec("cd " . base_path() . " && php composer.phar dump-autoload --optimize 2>&1", $output, $returnCode);
            } else {
                exec("cd " . base_path() . " && composer dump-autoload --optimize 2>&1", $output, $returnCode);
            }
            
            $results['step_6_composer_regenerate'] = [
                'status' => $returnCode === 0 ? 'SUCCESS' : 'FAILED',
                'return_code' => $returnCode,
                'output' => implode("\n", $output)
            ];
            
            // Step 7: Verify User.php is accessible
            $userFile = app_path('Models/User.php');
            $results['step_7_user_file_check'] = [
                'exists' => file_exists($userFile),
                'readable' => is_readable($userFile),
                'size' => file_exists($userFile) ? filesize($userFile) : 0,
                'modified' => file_exists($userFile) ? date('Y-m-d H:i:s', filemtime($userFile)) : null
            ];
            
            $allSuccess = empty($errors) && 
                         $returnCode === 0 && 
                         file_exists($userFile);
            
            return response()->json([
                'thanh_cong' => $allSuccess,
                'thong_bao' => $allSuccess 
                    ? '🎉 NUCLEAR RESET HOÀN TẤT! Tất cả cache và autoloader đã được xóa và tạo lại.'
                    : '⚠️ RESET HOÀN TẤT NHƯNG CÓ MỘT SỐ LỖI. Xem chi tiết bên dưới.',
                'chi_tiet' => $results,
                'loi' => $errors,
                'buoc_tiep_theo' => [
                    '1' => 'Refresh trình duyệt với Ctrl+F5 (hoặc Cmd+Shift+R)',
                    '2' => 'Truy cập trang chủ: ' . url('/'),
                    '3' => 'Nếu vẫn lỗi "Cannot declare class", liên hệ hosting với tin nhắn bên dưới'
                ],
                'tin_nhan_gui_hosting' => !$allSuccess ? [
                    'subject' => 'URGENT: Cannot Declare Class Error - Need Server Restart',
                    'body' => "Dear Support Team,\n\n" .
                        "I have performed a complete application-level reset (nuclear reset) but still experiencing 'Cannot declare class App\\Models\\User' error.\n\n" .
                        "Domain: " . request()->getHost() . "\n" .
                        "PHP Version: " . PHP_VERSION . "\n" .
                        "Framework: Laravel 11\n\n" .
                        "Actions Already Taken:\n" .
                        "✅ Deleted all bootstrap cache files\n" .
                        "✅ Cleared Laravel caches (config, route, view, compiled)\n" .
                        "✅ Cleared file cache\n" .
                        "✅ Attempted composer dump-autoload\n" .
                        "✅ Attempted OPcache reset\n\n" .
                        "Request: This error requires SERVER-LEVEL intervention:\n" .
                        "1. Restart PHP-FPM service completely\n" .
                        "2. Clear PHP OPcache at server level\n" .
                        "3. Verify no stale PHP processes are running\n" .
                        "4. Check if there are any server-level caching (Varnish, Redis, etc.)\n" .
                        "5. Verify file permissions on app/Models/User.php\n\n" .
                        "This is a critical production issue preventing the application from running.\n\n" .
                        "Thank you for your urgent assistance!"
                ] : null,
                'luu_y' => 'Nuclear reset đã xóa TẤT CẢ cache và regenerate autoloader. Nếu vẫn lỗi, vấn đề nằm ở server/hosting level.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'thanh_cong' => false,
                'loi' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'huong_dan' => 'Lỗi nghiêm trọng. Liên hệ hosting ngay lập tức.'
            ]);
        }
    });
});
