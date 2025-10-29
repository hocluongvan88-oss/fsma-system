<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\ForceHttps::class,
        \App\Http\Middleware\SecurityHeaders::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetCharacterEncoding::class,
            \App\Http\Middleware\SessionTimeout::class,
            \App\Http\Middleware\ConcurrentSessionControl::class,
            \App\Http\Middleware\AuditTrail::class,
            \App\Http\Middleware\EnforceTenantIsolation::class,
            \App\Http\Middleware\SetTenantContext::class,
            \App\Http\Middleware\SetAuditContext::class,
        ],
        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\Admin::class,
        'admin.or.manager' => \App\Http\Middleware\AdminOrManager::class,
        'system.admin' => \App\Http\Middleware\SystemAdminMiddleware::class,
        'operator' => \App\Http\Middleware\CheckOperatorAccess::class,
        'check.permission' => \App\Http\Middleware\CheckPermission::class,
        'check.menu.access' => \App\Http\Middleware\CheckMenuAccess::class,
        'check.package' => \App\Http\Middleware\CheckPackage::class,
        'check.package.feature' => \App\Http\Middleware\CheckPackageFeature::class,
        'check.package.feature.quota' => \App\Http\Middleware\CheckPackageFeatureWithQuota::class,
        'check.package.limit' => \App\Http\Middleware\CheckPackageLimit::class,
        'check.organization.access' => \App\Http\Middleware\CheckOrganizationAccess::class,
        'ensure.organization.access' => \App\Http\Middleware\EnsureOrganizationAccess::class,
        'ensure.tenant.access' => \App\Http\Middleware\EnsureTenantAccess::class,
        'validate.tenant.access' => \App\Http\Middleware\ValidateTenantAccess::class,
        'rate.limit.payment' => \App\Http\Middleware\RateLimitPayment::class,
        'rate.limit.signatures' => \App\Http\Middleware\RateLimitSignatures::class,
        'rate.limit.trace' => \App\Http\Middleware\RateLimitTraceability::class,
        'rate.limit.webhook' => \App\Http\Middleware\RateLimitWebhook::class,
        'rate.limit.login' => \App\Http\Middleware\RateLimitLogin::class,
    ];
}
