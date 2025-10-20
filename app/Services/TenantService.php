<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantTrash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class TenantService {
    /**
     * Создать нового тенанта с отдельной БД
     */
    public function createTenant(array $data): Tenant {
        return DB::transaction(function () use ($data) {
            // Создаем запись тенанта
            $tenant = Tenant::create($data);

            // Создаем БД для тенанта
            $this->createTenantDatabase($tenant);

            // Применяем миграции для тенанта
            $this->runTenantMigrations($tenant);

            return $tenant;
        });
    }

    /**
     * Создать БД для тенанта
     */
    public function createTenantDatabase(Tenant $tenant): void {
        $database = $tenant->database;

        // Создаем БД через SQL
        DB::statement("CREATE DATABASE \"{$database}\"");

        // Создаем расширения для JSONB
        $this->createDatabaseExtensions($database);
    }

    /**
     * Создать расширения в БД тенанта
     */
    private function createDatabaseExtensions(string $database): void {
        // Подключаемся к БД тенанта
        $originalConnection = DB::getDefaultConnection();

        Config::set('database.connections.tenant_temp', [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => $database,
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);

        DB::setDefaultConnection('tenant_temp');

        // Создаем расширения если нужно
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        } catch (\Exception $e) {
            // Расширение уже существует или нет прав
        }

        // Возвращаем исходное подключение
        DB::setDefaultConnection($originalConnection);
    }

    /**
     * Применить миграции для тенанта
     */
    public function runTenantMigrations(Tenant $tenant): void {
        $originalConnection = DB::getDefaultConnection();

        // Настраиваем подключение к БД тенанта
        Config::set('database.connections.tenant_migration', [
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

        // Запускаем миграции для тенанта
        Artisan::call('migrate', [
            '--database' => 'tenant_migration',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        // Возвращаем исходное подключение
        DB::setDefaultConnection($originalConnection);
    }

    /**
     * Мягкое удаление тенанта (в корзину)
     */
    public function softDeleteTenant(Tenant $tenant, ?string $reason = null): void {
        DB::transaction(function () use ($tenant, $reason) {
            // Помечаем тенанта как удаленного
            $tenant->markAsDeleted();

            // Добавляем запись в корзину
            TenantTrash::create([
                'tenant_id' => $tenant->id,
                'deleted_by' => Auth::id(),
                'deleted_at' => now(),
                'deletion_reason' => $reason,
            ]);
        });
    }

    /**
     * Восстановить тенанта из корзины
     */
    public function restoreTenant(Tenant $tenant): void {
        DB::transaction(function () use ($tenant) {
            // Восстанавливаем тенанта
            $tenant->restore();

            // Удаляем запись из корзины
            $tenant->trash()->delete();
        });
    }

    /**
     * Полное удаление тенанта (из корзины)
     */
    public function forceDeleteTenant(Tenant $tenant): void {
        DB::transaction(function () use ($tenant) {
            // Удаляем БД тенанта
            $this->dropTenantDatabase($tenant);

            // Удаляем запись из корзины
            $tenant->trash()->delete();

            // Полностью удаляем запись тенанта
            $tenant->delete();
        });
    }

    /**
     * Старый метод для полного удаления (оставляем для совместимости)
     */
    public function deleteTenant(Tenant $tenant): void {
        $this->forceDeleteTenant($tenant);
    }

    /**
     * Удалить БД тенанта
     */
    private function dropTenantDatabase(Tenant $tenant): void {
        $database = $tenant->database;

        // Закрываем все соединения с БД
        DB::statement("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ?", [$database]);

        // Удаляем БД
        DB::statement("DROP DATABASE IF EXISTS \"{$database}\"");
    }
}
