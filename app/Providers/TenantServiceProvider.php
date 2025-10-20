<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class TenantServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        // Настраиваем подключение к базе данных тенанта при каждом запросе
        $this->app->booted(function () {
            $this->configureTenantDatabase();
        });
    }

    /**
     * Настройка подключения к базе данных тенанта
     */
    protected function configureTenantDatabase(): void {
        // Получаем tenant_id из сессии
        $tenantId = session('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if ($tenant && !$tenant->deleted) {
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
            }
        }
    }
}
