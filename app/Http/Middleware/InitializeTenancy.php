<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class InitializeTenancy {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        // Определяем домен из запроса
        $host = $request->getHost();

        // Ищем тенанта по домену
        $tenant = Tenant::where('domain', $host)->first();

        if ($tenant && $tenant->status === 'active') {
            // Настраиваем подключение к БД тенанта
            Config::set('database.connections.tenant', [
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
            ]);

            // Устанавливаем БД тенанта как основную
            DB::setDefaultConnection('tenant');

            // Добавляем тенанта в request для использования в контроллерах
            $request->attributes->set('tenant', $tenant);
        } else {
            // Если тенант не найден, используем центральную БД
            DB::setDefaultConnection('pgsql');
        }

        return $next($request);
    }
}
