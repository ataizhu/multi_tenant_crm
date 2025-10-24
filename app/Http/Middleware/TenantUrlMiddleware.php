<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantUrlMiddleware {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        // Приоритет: параметр в URL > сессия
        $tenantId = $request->get('tenant');

        if (!$tenantId && session()->has('current_tenant')) {
            $tenant = session('current_tenant');
            $tenantId = is_object($tenant) ? $tenant->id : $tenant;
        }

        // Если есть tenant ID, но его нет в URL, добавляем его
        if ($tenantId && !$request->has('tenant')) {
            $url = $request->fullUrl();
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $newUrl = $url . $separator . 'tenant=' . $tenantId;

            return redirect($newUrl);
        }

        return $next($request);
    }
}
