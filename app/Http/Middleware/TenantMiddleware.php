<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tenantId = auth()->user()->tenant_id;
        app()->instance('tenant_id', $tenantId);
        return $next($request);
    }
}
