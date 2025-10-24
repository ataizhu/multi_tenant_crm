<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantDatabaseMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Определяем tenant по параметру в URL или сессии
        $tenantId = $this->getTenantId($request);

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if ($tenant && !$tenant->deleted) {
                // Настраиваем подключение к БД tenant
                config([
                    'database.connections.tenant' => [
                        'driver' => 'pgsql',
                        'host' => env('DB_HOST', '127.0.0.1'),
                        'port' => env('DB_PORT', '5432'),
                        'database' => $tenant->database,
                        'username' => env('DB_USERNAME', 'postgres'),
                        'password' => env('DB_PASSWORD', ''),
                        'charset' => 'utf8',
                        'prefix' => '',
                        'prefix_indexes' => true,
                        'search_path' => 'public',
                        'sslmode' => 'prefer',
                    ]
                ]);

                // Сохраняем tenant в сессии для брендинга
                session(['current_tenant' => $tenant]);

                // Сохраняем тенанта в запросе
                $request->attributes->set('tenant', $tenant);
            }
        }

        return $next($request);
    }

    /**
     * Определяем ID tenant различными способами
     */
    private function getTenantId(Request $request): ?int {
        // 1. По параметру в URL (?tenant=9)
        if ($request->has('tenant')) {
            $tenant = $request->get('tenant');
            // Если это объект Tenant, берем его ID
            if (is_object($tenant) && $tenant instanceof \App\Models\Tenant) {
                return $tenant->id;
            }
            // Если это строка или число, конвертируем в int
            return (int) $tenant;
        }

        // 2. По поддомену (test.localhost)
        $host = $request->getHost();
        if (strpos($host, '.') !== false) {
            $subdomain = explode('.', $host)[0];
            if ($subdomain !== 'localhost' && $subdomain !== '127') {
                return Tenant::where('domain', $subdomain)->value('id');
            }
        }

        // 3. По сессии (если уже выбран tenant)
        if (session()->has('current_tenant')) {
            $tenant = session('current_tenant');
            return is_object($tenant) ? $tenant->id : (int) $tenant;
        }

        return null;
    }
}
