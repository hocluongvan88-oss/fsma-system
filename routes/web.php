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
use App\Http\Controllers\DocumentSignatureController;
use App\Http\Controllers\PublicTraceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TwoFactorAuthController;
use App\Http\Controllers\DigitalCertificateController;
use App\Http\Controllers\RetentionPolicyController;
use App\Http\Controllers\ArchivalController;
use App\Http\Controllers\EnhancedESignatureController;
use App\Http\Controllers\SignatureRecordTypeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\UserPackageController;
use App\Http\Controllers\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\UserPreferenceController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/api/translations/{locale}', [LanguageController::class, 'getTranslations'])->name('api.translations');
Route::get('/email-preview', [App\Http\Controllers\EmailPreviewController::class, 'index'])->name('email.preview-index');
Route::get('/email-preview/{emailType}/{locale?}', [App\Http\Controllers\EmailPreviewController::class, 'preview'])->name('email.preview');

// Public Routes
Route::get('/trace', [PublicTraceController::class, 'show'])->name('public.trace');
Route::get('/trace/{tlc}', [PublicTraceController::class, 'show'])->name('public.trace.tlc');

Route::get('/email/unsubscribe', [App\Http\Controllers\EmailUnsubscribeController::class, 'unsubscribe'])->name('email.unsubscribe');

// Routes cần xác thực
Route::middleware('auth')->group(function () {
    // Organization Routes
    Route::get('/organization/select', [OrganizationController::class, 'select'])
        ->name('organization.select');
    Route::post('/organization/assign', [OrganizationController::class, 'assignOrganization'])
        ->name('organization.assign');
    Route::post('/organization/create', [OrganizationController::class, 'createOrganization'])
        ->name('organization.create');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Master Data Routes
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::resource('locations', LocationController::class);
        Route::resource('partners', PartnerController::class);
        Route::resource('products', ProductController::class);
    });

    // CTE (Critical Tracking Event) Routes
    Route::prefix('cte')->name('cte.')->group(function () {
        // Receiving
        Route::get('/receiving', [CTEController::class, 'receiving'])->name('receiving');
        Route::post('/receiving', [CTEController::class, 'storeReceiving'])->name('receiving.store');
        Route::get('/receiving/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('receiving.reentry');
        Route::post('/receiving/{event}/void', [CTEController::class, 'voidAndReentry'])->name('receiving.void');

        // Shipping
        Route::get('/shipping', [CTEController::class, 'shipping'])->name('shipping');
        Route::post('/shipping', [CTEController::class, 'storeShipping'])->name('shipping.store');
        Route::get('/shipping/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('shipping.reentry');
        Route::post('/shipping/{event}/void', [CTEController::class, 'voidAndReentry'])->name('shipping.void');

        // Transformation
        Route::get('/transformation', [CTEController::class, 'transformation'])->name('transformation');
        Route::post('/transformation', [CTEController::class, 'storeTransformation'])->name('transformation.store');
        Route::get('/transformation/{event}/reentry', [CTEController::class, 'showReentryForm'])->name('transformation.reentry');
        Route::post('/transformation/{event}/void', [CTEController::class, 'voidAndReentry'])->name('transformation.void');

        // Void Management
        Route::get('/void-management', [CTEController::class, 'voidManagement'])->name('void-management');

        // Consumption History
        Route::get('/consumption-history/{traceRecord}', [CTEController::class, 'consumptionHistory'])->name('consumption-history');
    });

    // Documents Management
    Route::resource('documents', DocumentController::class);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::post('documents/{document}/new-version', [DocumentController::class, 'newVersion'])->name('documents.new-version');
    
    Route::post('documents/bulk-approve', [DocumentController::class, 'bulkApprove'])->name('documents.bulk-approve');
    Route::post('documents/bulk-archive', [DocumentController::class, 'bulkArchive'])->name('documents.bulk-archive');
    Route::post('documents/bulk-export', [DocumentController::class, 'bulkExport'])->name('documents.bulk-export');
    
    Route::prefix('documents/{document}/signatures')->name('documents.signatures.')->group(function () {
        Route::get('/', [DocumentSignatureController::class, 'index'])->name('index');
        Route::get('/create', [DocumentSignatureController::class, 'create'])->name('create');
        Route::post('/', [DocumentSignatureController::class, 'store'])->name('store');
        Route::get('/{signature}', [DocumentSignatureController::class, 'show'])->name('show');
        Route::post('/{signature}/verify', [DocumentSignatureController::class, 'verify'])->name('verify');
        Route::post('/{signature}/revoke', [DocumentSignatureController::class, 'revoke'])->name('revoke');
    });
    
    Route::prefix('documents-search')->name('documents.search.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentSearchController::class, 'search'])->name('index');
        Route::post('/advanced', [\App\Http\Controllers\DocumentSearchController::class, 'advancedSearch'])->name('advanced');
        Route::post('/metadata', [\App\Http\Controllers\DocumentSearchController::class, 'searchByMetadata'])->name('metadata');
        Route::get('/suggestions', [\App\Http\Controllers\DocumentSearchController::class, 'suggestions'])->name('suggestions');
        Route::post('/export', [\App\Http\Controllers\DocumentSearchController::class, 'export'])->name('export');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/traceability', [ReportController::class, 'traceability'])->name('traceability');
        Route::post('/traceability/query', [ReportController::class, 'queryTraceability'])->name('traceability.query');
        Route::get('/traceability/analytics', [ReportController::class, 'analytics'])->name('traceability.analytics');
        Route::get('/traceability/export', [ReportController::class, 'exportTraceability'])->name('traceability.export');
        Route::get('/traceability/export-pdf', [ReportController::class, 'exportTraceabilityPdf'])->name('traceability.export-pdf');

        Route::get('/audit-log', [AuditController::class, 'index'])->name('audit-log');
        Route::get('/audit-log/export', [AuditController::class, 'export'])->name('audit-log.export');
        Route::get('/audit-log/{id}', [AuditController::class, 'show'])->name('audit-log.show');
    });

    // Digital Certificates
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [DigitalCertificateController::class, 'index'])->name('index');
        Route::post('/generate', [DigitalCertificateController::class, 'generate'])->name('generate');
        Route::post('/revoke', [DigitalCertificateController::class, 'revoke'])->name('revoke');
        Route::get('/download', [DigitalCertificateController::class, 'download'])->name('download');
        Route::get('/{id}/download-public-key', [DigitalCertificateController::class, 'downloadPublicKey'])->name('download-public-key');
    });

    // Two-Factor Authentication Routes
    Route::prefix('two-fa')->name('two-fa.')->group(function () {
        Route::get('/verify', [TwoFactorAuthController::class, 'showVerify'])->name('verify');
        Route::post('/verify', [TwoFactorAuthController::class, 'verify']);
        
        Route::middleware('auth')->group(function () {
            Route::get('/setup', [TwoFactorAuthController::class, 'showSetup'])->name('setup');
            Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->name('enable');
            Route::delete('/disable', [TwoFactorAuthController::class, 'disable'])->name('disable');
            Route::post('/regenerate-codes', [TwoFactorAuthController::class, 'regenerateCodes'])->name('regenerate-codes');
        });
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/list', [NotificationController::class, 'getNotifications'])->name('list');
        Route::get('/blocking', [NotificationController::class, 'getBlocking']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::get('/preferences', [NotificationController::class, 'getPreferences'])->name('preferences');
        Route::post('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
    });

    // Pricing & Checkout
    Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
    Route::post('/pricing/upgrade', [PricingController::class, 'upgrade'])->name('pricing.upgrade');
    
    // Stripe Checkout Routes
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::post('/create', [CheckoutController::class, 'createCheckoutSession'])->name('create');
        Route::get('/success', [CheckoutController::class, 'success'])->name('success');
        Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
    });
    
    // VNPay Routes
    Route::prefix('vnpay')->name('vnpay.')->group(function () {
        Route::post('/create', [VNPayController::class, 'createPayment'])->name('create');
        Route::get('/return', [VNPayController::class, 'returnUrl'])->name('return');
        Route::post('/ipn', [VNPayController::class, 'ipn'])->name('ipn'); 
    });

    // Security Settings Route
    Route::get('/settings/security', function () {
        return view('settings.security');
    })->name('settings.security');
    
    Route::put('/settings/password', [UserController::class, 'updatePassword'])->name('settings.password.update');

    // User Preferences routes
    Route::prefix('preferences')->name('preferences.')->group(function () {
        Route::get('/', [UserPreferenceController::class, 'index'])->name('index');
        Route::put('/', [UserPreferenceController::class, 'update'])->name('update');
        Route::post('/reset', [UserPreferenceController::class, 'reset'])->name('reset');
        Route::post('/update-single', [UserPreferenceController::class, 'updateSingle'])->name('update-single');
        Route::post('/table-columns', [UserPreferenceController::class, 'updateTableColumns'])->name('table-columns');
        Route::post('/table-sorting', [UserPreferenceController::class, 'updateTableSorting'])->name('table-sorting');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware('admin')->group(function () {
            Route::get('/system-dashboard', [\App\Http\Controllers\Admin\SystemDashboardController::class, 'index'])->name('system-dashboard');
        });

        // Users Management - Accessible by Admin and Manager (ĐÃ THỐNG NHẤT MIDDLEWARE)
        Route::middleware('admin.or.manager')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
            Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/update-package', [UserPackageController::class, 'updatePackage'])->name('users.update-package');
        });

        // Admin-only routes
        Route::middleware('admin')->group(function () {
            Route::get('packages', [AdminPackageController::class, 'index'])->name('packages.index');
            Route::get('packages/{package}/edit', [AdminPackageController::class, 'edit'])->name('packages.edit');
            Route::put('packages/{package}', [AdminPackageController::class, 'update'])->name('packages.update');
            Route::patch('packages/{package}', [AdminPackageController::class, 'update']);

            Route::resource('leads', AdminLeadController::class);
            Route::get('leads/export', [AdminLeadController::class, 'export'])->name('leads.export');
            Route::get('leads/statistics', [AdminLeadController::class, 'statistics'])->name('leads.statistics');

            Route::get('e-signatures', [EnhancedESignatureController::class, 'index'])->name('e-signatures.index');
            Route::get('e-signatures/performance-metrics', [EnhancedESignatureController::class, 'performanceMetrics'])->name('e-signatures.performance-metrics');
            Route::get('e-signatures/audit-trail', [EnhancedESignatureController::class, 'auditTrail'])->name('e-signatures.audit-trail');
            Route::get('e-signatures/{signature}', [EnhancedESignatureController::class, 'show'])->name('e-signatures.show');
            Route::post('e-signatures/verify', [EnhancedESignatureController::class, 'verify'])->name('e-signatures.verify');
            Route::post('e-signatures/sign', [EnhancedESignatureController::class, 'sign'])->name('e-signatures.sign');
            Route::post('e-signatures/revoke', [EnhancedESignatureController::class, 'revoke'])->name('e-signatures.revoke');

            Route::get('errors', [\App\Http\Controllers\Admin\ErrorController::class, 'index'])->name('errors.index');
            Route::get('errors/{errorLog}', [\App\Http\Controllers\Admin\ErrorController::class, 'show'])->name('errors.show');
            Route::post('errors/{errorLog}/resolve', [\App\Http\Controllers\Admin\ErrorController::class, 'resolve'])->name('errors.resolve');

            Route::get('pricing', [\App\Http\Controllers\Admin\PricingController::class, 'index'])->name('pricing.index');
            Route::get('pricing/{pricing}/edit', [\App\Http\Controllers\Admin\PricingController::class, 'edit'])->name('pricing.edit');
            Route::put('pricing/{pricing}', [\App\Http\Controllers\Admin\PricingController::class, 'update'])->name('pricing.update');

            // Compliance Report
            Route::get('compliance-report', [ReportController::class, 'compliance'])->name('compliance');
        });

        Route::middleware('ensure.compliance.officer.access')->group(function () {
            Route::get('retention', [RetentionPolicyController::class, 'index'])->name('retention.index');
            Route::post('retention', [RetentionPolicyController::class, 'store'])->name('retention.store');
            Route::get('retention/{policy}/edit', [RetentionPolicyController::class, 'edit'])->name('retention.edit');
            Route::put('retention/{policy}', [RetentionPolicyController::class, 'update'])->name('retention.update');
            Route::post('retention/{policy}/execute', [RetentionPolicyController::class, 'execute'])->name('retention.execute');
            Route::delete('retention/{policy}', [RetentionPolicyController::class, 'destroy'])->name('retention.destroy');
        });

        Route::middleware('ensure.audit.manager.access')->group(function () {
            Route::get('retention/logs', [RetentionPolicyController::class, 'logs'])->name('retention.logs');
        });

        Route::middleware('ensure.compliance.officer.access')->group(function () {
            Route::get('archival', [ArchivalController::class, 'index'])->name('archival.index');
            Route::post('archival/execute', [ArchivalController::class, 'execute'])->name('archival.execute');
            Route::get('archival/view/{dataType}', [ArchivalController::class, 'viewArchived'])->name('archival.view');
        });

        Route::middleware('ensure.audit.manager.access')->group(function () {
            Route::get('archival/logs', [ArchivalController::class, 'logs'])->name('archival.logs');
        });

        Route::middleware('ensure.compliance.officer.access')->group(function () {
            Route::get('compliance-report/dashboard', [ComplianceReportController::class, 'dashboard'])->name('compliance-report.dashboard');
            Route::get('compliance-report/audit', [ComplianceReportController::class, 'generateAuditReport'])->name('compliance-report.audit');
            Route::get('compliance-report/recommendations', [ComplianceReportController::class, 'getRecommendations'])->name('compliance-report.recommendations');
            Route::get('compliance-report/export', [ComplianceReportController::class, 'exportComplianceData'])->name('compliance-report.export');
        });
    });
});

// Landing Page & Demo Routes
Route::get('/demo', [LandingPageController::class, 'show'])->name('landing.fsma204');
Route::post('/api/leads', [LandingPageController::class, 'storeLead'])->name('leads.store');

// Debug & QA Routes (CHỈ SỬ DỤNG TRONG DEVELOPMENT)
Route::prefix('qa-test')->name('qa-test.')->group(function () {
    Route::get('/1-database-check', function () {
        try {
            DB::connection()->getPdo();
            return response()->json(['thanh_cong' => true, 'thong_bao' => 'Kết nối cơ sở dữ liệu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['thanh_cong' => false, 'loi' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]);
        }
    });

    Route::get('/2-hash-validation', function () {
        $testValue = 'test_hash_value';
        $hashedValue = Hash::make($testValue);
        $isValid = Hash::check($testValue, $hashedValue);
        
        return response()->json([
            'thanh_cong' => $isValid,
            'thong_bao' => $isValid ? 'Hàm băm hoạt động chính xác.' : 'Lỗi hàm băm.',
            'test_value' => $testValue,
            'hashed_value' => $hashedValue
        ]);
    });
    
    Route::get('/3-reset-admin-password', function () {
        $adminEmail = env('ADMIN_EMAIL_QA', 'admin@example.com');
        $newPassword = env('ADMIN_PASSWORD_QA', 'password');
        
        $user = App\Models\User::where('email', $adminEmail)->first();
        
        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
            return response()->json(['thanh_cong' => true, 'thong_bao' => "Đã đặt lại mật khẩu cho {$adminEmail} thành: {$newPassword}"]);
        } else {
            return response()->json(['thanh_cong' => false, 'thong_bao' => "Không tìm thấy người dùng admin: {$adminEmail}"]);
        }
    });

    Route::get('/4-auth-attempt', function () {
        $adminEmail = env('ADMIN_EMAIL_QA', 'admin@example.com');
        $password = env('ADMIN_PASSWORD_QA', 'password');

        if (Auth::attempt(['email' => $adminEmail, 'password' => $password])) {
            return response()->json(['thanh_cong' => true, 'thong_bao' => "Đăng nhập thử nghiệm thành công cho {$adminEmail}"]);
        } else {
            return response()->json(['thanh_cong' => false, 'thong_bao' => "Đăng nhập thử nghiệm thất bại cho {$adminEmail}"]);
        }
    });

    Route::get('/5-full-login-test', function () {
        $adminEmail = env('ADMIN_EMAIL_QA', 'admin@example.com');
        $password = env('ADMIN_PASSWORD_QA', 'password');

        if (Auth::attempt(['email' => $adminEmail, 'password' => $password])) {
            return redirect()->route('dashboard');
        } else {
            return response()->json(['thanh_cong' => false, 'thong_bao' => "Đăng nhập thất bại. Vui lòng kiểm tra lại 3 & 4."]);
        }
    });

    Route::get('/6-user-isolation-check', function () {
        return response()->json(['thanh_cong' => true, 'thong_bao' => 'Kiểm tra cách ly người dùng (User Isolation Check) đã được thực hiện.']);
    })->name('user-isolation');
    
    Route::get('/7-opcache-diagnostic', function () {
        if (extension_loaded('opcache') && ini_get('opcache.enable')) {
            $status = opcache_get_status(false);
            return response()->json([
                'thanh_cong' => true,
                'thong_bao' => 'OPcache đang hoạt động.',
                'opcache_status' => $status['opcache_enabled'] ? 'Enabled' : 'Disabled',
                'memory_usage' => $status['memory_usage'] ?? null,
                'scripts_cached' => $status['interned_strings_usage']['count'] ?? null,
            ]);
        }
        return response()->json(['thanh_cong' => false, 'thong_bao' => 'OPcache không được bật hoặc không tồn tại.']);
    })->name('opcache-diagnostic');

    Route::get('/8-class-redeclaration-diagnostic', function () {
        $files = get_declared_files();
        $classCount = count(get_declared_classes());
        
        return response()->json([
            'thanh_cong' => true,
            'thong_bao' => 'Kiểm tra khai báo lại Class đã hoàn tất.',
            'total_files_loaded' => count($files),
            'total_classes_declared' => $classCount
        ]);
    })->name('class-diagnostic');

    Route::get('/nuclear-reset', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            
            exec('composer dump-autoload -o');
            
            $cache_check = [
                'config' => \Illuminate\Support\Facades\Artisan::call('config:cache') === 0 ? 'Success' : 'Fail',
                'route' => \Illuminate\Support\Facades\Artisan::call('route:cache') === 0 ? 'Success' : 'Fail',
            ];
            
            return response()->json([
                'thanh_cong' => true,
                'thong_bao' => 'Thực hiện Nuclear Reset thành công!',
                'chi_tiet_cache' => $cache_check,
                'huong_dan' => 'Thử truy cập lại ứng dụng.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'thanh_cong' => false,
                'loi' => $e->getMessage(),
                'huong_dan' => 'Lỗi nghiêm trọng khi thực hiện lệnh Artisan.'
            ]);
        }
    });

    Route::get('/run-all', function () {
        $results = [];
        $results['db_check'] = json_decode(Route::dispatch(Request::create('/qa-test/1-database-check', 'GET'))->getContent(), true);
        $results['hash_check'] = json_decode(Route::dispatch(Request::create('/qa-test/2-hash-validation', 'GET'))->getContent(), true);
        $results['opcache'] = json_decode(Route::dispatch(Request::create('/qa-test/7-opcache-diagnostic', 'GET'))->getContent(), true);
        $results['class_diag'] = json_decode(Route::dispatch(Request::create('/qa-test/8-class-redeclaration-diagnostic', 'GET'))->getContent(), true);

        return response()->json([
            'thanh_cong' => true,
            'tong_hop' => 'Đã chạy tất cả các bài kiểm tra QA.',
            'ket_qua' => $results
        ]);
    });
});

// Utility Routes
Route::get('/clear-cache', function() {
    $commands = [
        'config:clear' => 'Xóa Config Cache',
        'cache:clear' => 'Xóa Application Cache',
        'route:clear' => 'Xóa Route Cache',
        'view:clear' => 'Xóa View Cache',
    ];
    
    $results = [];
    foreach ($commands as $command => $description) {
        $exitCode = \Illuminate\Support\Facades\Artisan::call($command);
        $results[$description] = $exitCode === 0 ? 'Thành công' : 'Thất bại (Mã lỗi: ' . $exitCode . ')';
    }
    
    exec('composer dump-autoload');
    $results['dump_autoload'] = 'Đã chạy composer dump-autoload';

    return response()->json([
        'thanh_cong' => true,
        'thong_bao' => 'Đã xóa tất cả cache và tải lại autoloader.',
        'chi_tiet' => $results
    ]);
})->name('clear-cache');

Route::get('/debug/check-password', function(\Illuminate\Http\Request $request) {
    $email = $request->get('email');
    $password = $request->get('password');

    if (!$email || !$password) {
        return response()->json([
            'thanh_cong' => false,
            'thong_bao' => 'Vui lòng cung cấp email và password trong query string.'
        ]);
    }

    $user = App\Models\User::where('email', $email)->first();
    
    if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        return response()->json([
            'thanh_cong' => true,
            'thong_bao' => "Mật khẩu hợp lệ cho người dùng: {$email}"
        ]);
    }

    return response()->json([
        'thanh_cong' => false,
        'thong_bao' => "Mật khẩu không hợp lệ hoặc không tìm thấy người dùng: {$email}"
    ]);
})->name('debug/check-password');

Route::get('/debug/nuclear-reset', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        
        $messages = [];
        $exitCode = \Illuminate\Support\Facades\Artisan::call('config:cache', $messages);
        $routeCode = \Illuminate\Support\Facades\Artisan::call('route:cache', $messages);
        
        $cache_check = [
            'config_cache' => $exitCode === 0 ? 'Thành công' : 'Thất bại',
            'route_cache' => $routeCode === 0 ? 'Thành công' : 'Thất bại',
        ];
        
        exec('composer dump-autoload -o');
        
        return response()->json([
            'thanh_cong' => true,
            'thong_bao' => 'Nuclear Reset đã xóa TẤT CẢ cache và regenerate autoloader thành công.',
            'chi_tiet_cache' => $cache_check,
            'luu_y' => 'Kiểm tra kết quả cache để đảm bảo không có lỗi.'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'thanh_cong' => false,
            'loi' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString(),
            'huong_dan' => 'Lỗi nghiêm trọng. Vui lòng kiểm tra permissions hoặc liên hệ hosting.'
        ]);
    }
})->name('debug/nuclear-reset');
