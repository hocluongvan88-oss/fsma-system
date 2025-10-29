<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\PaymentOrder;
use Illuminate\Support\Facades\Auth;

class PaymentAuditService
{
    /**
     * Log payment action
     */
    public function logPaymentAction(
        string $action,
        PaymentOrder $paymentOrder,
        ?Organization $organization = null,
        array $details = []
    ): void {
        $user = Auth::user();
        $org = $organization ?? $paymentOrder->organization;

        AuditLog::create([
            'organization_id' => $org?->id,
            'user_id' => $user?->id,
            'action' => "payment.{$action}",
            'model_type' => PaymentOrder::class,
            'model_id' => $paymentOrder->id,
            'old_values' => $details['old_values'] ?? null,
            'new_values' => $details['new_values'] ?? null,
            'description' => $details['description'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log package change
     */
    public function logPackageChange(
        Organization $organization,
        string $oldPackageId,
        string $newPackageId,
        array $details = []
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'organization_id' => $organization->id,
            'user_id' => $user?->id,
            'action' => 'package.changed',
            'model_type' => Organization::class,
            'model_id' => $organization->id,
            'old_values' => ['package_id' => $oldPackageId],
            'new_values' => ['package_id' => $newPackageId],
            'description' => "Package changed from {$oldPackageId} to {$newPackageId}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log quota sync
     */
    public function logQuotaSync(Organization $organization, array $quotas): void
    {
        $user = Auth::user();

        AuditLog::create([
            'organization_id' => $organization->id,
            'user_id' => $user?->id,
            'action' => 'quota.synced',
            'model_type' => Organization::class,
            'model_id' => $organization->id,
            'new_values' => $quotas,
            'description' => 'Organization quotas synced with package limits',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
