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
        // Добавляем параметр tenant к URL, если он есть в сессии
        if (session()->has('current_tenant')) {
            $tenantId = session('current_tenant')->id;

            // Если параметр tenant отсутствует в запросе, добавляем его
            if (!$request->has('tenant')) {
                $url = $request->fullUrl();
                $separator = strpos($url, '?') !== false ? '&' : '?';
                $newUrl = $url . $separator . 'tenant=' . $tenantId;

                return redirect($newUrl);
            }
        }

        return $next($request);
    }
}
