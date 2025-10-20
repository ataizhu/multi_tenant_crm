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
        // Получаем tenant_id из URL или сессии
        $tenantId = $request->route('tenant_id') ?? session('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if ($tenant && !$tenant->deleted) {
                // Настраиваем подключение к БД тенанта
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

                // Сохраняем tenant_id в сессии
                session(['tenant_id' => $tenantId]);

                // Сохраняем тенанта в запросе
                $request->attributes->set('tenant', $tenant);
            }
        }

        return $next($request);
    }
}
