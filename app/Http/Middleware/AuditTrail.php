<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class AuditTrail
{
    protected AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only log POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logAudit($request, $response);
        }
        
        return $response;
    }
    
    protected function logAudit(Request $request, Response $response): void
    {
        try {
            $maskedData = $this->auditLogService->maskSensitiveData($request->all());

            $isCritical = $this->isCriticalOperation($request);

            $tableName = $this->extractTableName($request);
            
            if ($tableName === null && !$isCritical) {
                logger()->warning('Audit trail skipped - table name not determined', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);
                return;
            }
            
            if ($tableName === null) {
                $tableName = 'unknown_operation';
                logger()->warning('Audit trail using fallback table name', [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);
            }

            $this->auditLogService->log(
                $request->method() . ' ' . $request->path(),
                $tableName,
                $this->extractRecordId($request, $response),
                null,
                $maskedData
            );
        } catch (\Exception $e) {
            if ($this->isCriticalOperation($request)) {
                throw new \Exception('Audit logging failed for critical operation: ' . $e->getMessage());
            }
            
            // Log error but don't break non-critical requests
            logger()->error('Audit trail failed: ' . $e->getMessage(), [
                'path' => $request->path(),
                'method' => $request->method(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function isCriticalOperation(Request $request): bool
    {
        $criticalPaths = [
            'admin/users',
            'admin/packages',
            'e-signatures',
            'audit',
            'documents/delete',
        ];

        foreach ($criticalPaths as $path) {
            if (str_contains($request->path(), $path)) {
                return true;
            }
        }

        return false;
    }
    
    protected function extractTableName(Request $request): ?string
    {
        $path = $request->path();
        
        // Product routes
        if (str_contains($path, 'products')) return 'products';
        
        // Location routes
        if (str_contains($path, 'locations')) return 'locations';
        
        // Partner routes
        if (str_contains($path, 'partners')) return 'partners';
        
        // CTE routes
        if (str_contains($path, 'cte')) return 'cte_events';
        
        if (str_contains($path, 'e-signatures') || str_contains($path, 'e-signature')) {
            return 'e_signatures';
        }
        
        if (str_contains($path, 'documents')) return 'documents';
        
        if (str_contains($path, 'users')) return 'users';
        
        if (str_contains($path, 'packages')) return 'packages';
        
        if (str_contains($path, 'audit')) return 'audit_logs';
        
        if (str_contains($path, 'leads')) return 'leads';
        
        if (str_contains($path, 'organizations')) return 'organizations';
        
        if (str_contains($path, 'checkout') || str_contains($path, 'payment') || 
            str_contains($path, 'vnpay') || str_contains($path, 'stripe')) {
            return 'payment_orders';
        }
        
        if (str_contains($path, 'notifications')) return 'notifications';
        
        if (str_contains($path, 'reports')) return 'reports';
        
        return null;
    }
    
    protected function extractRecordId(Request $request, Response $response): ?int
    {
        // Try to get ID from route parameter
        $id = $request->route('id') ?? $request->route('product') ?? 
              $request->route('location') ?? $request->route('partner') ??
              $request->route('signature') ?? $request->route('document');
        
        if (is_object($id) && isset($id->id)) {
            return (int) $id->id;
        }
        
        if ($id && is_numeric($id)) {
            return (int) $id;
        }
        
        if ($request->isMethod('POST')) {
            // For e-signatures, try to get record_id from request
            if ($request->has('record_id')) {
                return (int) $request->input('record_id');
            }
            
            // For signature_id
            if ($request->has('signature_id')) {
                return (int) $request->input('signature_id');
            }
            
            // Try to extract from response for create operations
            if ($response->getStatusCode() === 201 || $response->getStatusCode() === 200) {
                $content = json_decode($response->getContent(), true);
                
                // Check various possible ID fields in response
                if (isset($content['id'])) {
                    return (int) $content['id'];
                }
                
                if (isset($content['signature']['id'])) {
                    return (int) $content['signature']['id'];
                }
                
                if (isset($content['data']['id'])) {
                    return (int) $content['data']['id'];
                }
            }
        }
        
        return null;
    }
}
