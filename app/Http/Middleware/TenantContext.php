<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Organization;

class TenantContext
{
    /**
     * Handle an incoming request.
     * 
     * Thiết lập tenant context cho mỗi request để đảm bảo:
     * 1. User chỉ có thể truy cập dữ liệu của organization của họ
     * 2. Admin có thể truy cập tất cả organizations
     * 3. Ngăn cross-tenant access attempts
     */
    public function handle(Request $request, Closure $next)
    {
        // Nếu user chưa authenticate, cho phép request tiếp tục (sẽ redirect ở Authenticate middleware)
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Nếu user không phải admin, kiểm tra organization_id có hợp lệ không
        if (!$user->isAdmin() && $user->organization_id) {
            $organization = Organization::find($user->organization_id);
            
            if (!$organization || !$organization->is_active) {
                Log::warning('Tenant access denied - organization not found or inactive', [
                    'user_id' => $user->id,
                    'organization_id' => $user->organization_id,
                    'ip' => $request->ip(),
                ]);
                
                auth()->logout();
                return redirect()->route('login')
                    ->with('error', 'Your organization is no longer active. Please contact support.');
            }
        }

        // Nếu request có organization_id parameter, kiểm tra user có quyền truy cập không
        if ($request->has('organization_id')) {
            $requestedOrgId = $request->input('organization_id');
            
            if (!$user->isAdmin() && $requestedOrgId != $user->organization_id) {
                Log::warning('Cross-tenant access attempt detected', [
                    'user_id' => $user->id,
                    'user_org_id' => $user->organization_id,
                    'requested_org_id' => $requestedOrgId,
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);
                
                abort(403, 'You do not have access to this organization.');
            }
        }

        // Kiểm tra route parameters có organization_id không
        if ($request->route()) {
            $routeOrgId = $request->route('organization_id');
            
            if ($routeOrgId && !$user->isAdmin() && $routeOrgId != $user->organization_id) {
                Log::warning('Cross-tenant route access attempt detected', [
                    'user_id' => $user->id,
                    'user_org_id' => $user->organization_id,
                    'route_org_id' => $routeOrgId,
                    'ip' => $request->ip(),
                    'route' => $request->route()->getName(),
                ]);
                
                abort(403, 'You do not have access to this organization.');
            }
        }

        // Lưu tenant context vào request để các services có thể sử dụng
        $request->attributes->set('tenant_id', $user->organization_id);
        $request->attributes->set('is_admin', $user->isAdmin());

        return $next($request);
    }
}
