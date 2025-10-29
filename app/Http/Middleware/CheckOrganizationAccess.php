<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;

class CheckOrganizationAccess
{
    /**
     * Handle an incoming request.
     * Ensures users can only access data from their own organization
     * Admin can access everything
     * Replaced isSystemAdmin() with isAdmin()
     * 
     * SECURITY FIX: Comprehensive resource checking with explicit organization validation
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin bypasses all organization checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        $resource = $this->getResourceFromRoute($request);
        
        if ($resource) {
            $tableName = $resource->getTable();
            
            // Check if table has organization_id column
            if (Schema::hasColumn($tableName, 'organization_id')) {
                if ($resource->organization_id !== $user->organization_id) {
                    // Log the unauthorized access attempt
                    \Illuminate\Support\Facades\Log::warning('Unauthorized organization access attempt', [
                        'user_id' => $user->id,
                        'user_org' => $user->organization_id,
                        'resource_type' => get_class($resource),
                        'resource_id' => $resource->id,
                        'resource_org' => $resource->organization_id,
                        'ip' => $request->ip(),
                    ]);
                    
                    abort(403, __('messages.cannot_access_other_organization_data'));
                }
            }
        }

        return $next($request);
    }

    /**
     * Get resource from route parameters
     * 
     * SECURITY FIX: Expanded to cover all resource types
     */
    private function getResourceFromRoute(Request $request)
    {
        $routeParams = [
            'user', 'document', 'product', 'location', 'partner', 
            'policy', 'event', 'signature', 'lead', 'notification',
            'traceRecord', 'cteEvent', 'auditLog'
        ];
        
        foreach ($routeParams as $param) {
            $resource = $request->route($param);
            if ($resource) {
                return $resource;
            }
        }

        return null;
    }
}
