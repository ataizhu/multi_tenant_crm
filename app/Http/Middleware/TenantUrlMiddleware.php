<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantUrlMiddleware {
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response {
        // Приоритет: параметр в URL > сессия
        $tenantParam = $request->get('tenant');

        // Если нет параметра tenant, пробуем определить по поддомену
        if (!$tenantParam) {
            $host = $request->getHost();
            if (strpos($host, '.') !== false) {
                $subdomain = explode('.', $host)[0];
                if ($subdomain !== 'localhost' && $subdomain !== '127') {
                    $tenantParam = $subdomain;
                }
            }
        }

        if (!$tenantParam && session()->has('current_tenant')) {
            $tenant = session('current_tenant');
            $tenantParam = is_object($tenant) ? $tenant->id : $tenant;
        }

        // Если есть параметр tenant, загружаем объект тенанта
        if ($tenantParam && !is_object($tenantParam)) {
            $tenant = $this->findTenant($tenantParam);
            if ($tenant) {
                $request->merge(['tenant' => $tenant]);
            }
        }

        return $next($request);
    }

    /**
     * Найти тенанта по ID или домену
     */
    private function findTenant($param): ?Tenant {
        // Если это числовой ID
        if (is_numeric($param)) {
            return Tenant::find($param);
        }

        // Если это строка (домен), ищем по полю domain
        if (is_string($param)) {
            return Tenant::where('domain', $param)->first();
        }

        return null;
    }
}
