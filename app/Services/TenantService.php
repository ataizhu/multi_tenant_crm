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
        // Создаем БД тенанта ВНЕ транзакции (PostgreSQL не позволяет CREATE DATABASE в транзакции)
        $tenant = Tenant::create($data);

        // Создаем БД для тенанта
        $this->createTenantDatabase($tenant);

        // Применяем миграции для тенанта в транзакции
        DB::transaction(function () use ($tenant) {
            $this->runTenantMigrations($tenant);
        });

        return $tenant;
    }

    /**
     * Создать БД для тенанта
     */
    public function createTenantDatabase(Tenant $tenant): void {
        $database = $tenant->database;

        try {
            // Создаем БД через прямое подключение (без транзакции)
            $pdo = new \PDO(
                "pgsql:host=" . env('DB_HOST', '127.0.0.1') . ";port=" . env('DB_PORT', '5432'),
                env('DB_USERNAME', 'postgres'),
                env('DB_PASSWORD', '')
            );

            $pdo->exec("CREATE DATABASE \"{$database}\"");

            // Создаем расширения для JSONB
            $this->createDatabaseExtensions($database);

        } catch (\Exception $e) {
            // Логируем ошибку и удаляем запись тенанта
            \Log::warning("Не удалось создать базу данных {$database}: " . $e->getMessage());
            $tenant->delete();
            throw $e;
        }
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
        // Удаляем БД тенанта ВНЕ транзакции (PostgreSQL не позволяет DROP DATABASE в транзакции)
        $this->dropTenantDatabase($tenant);

        // Удаляем записи из БД в транзакции
        DB::transaction(function () use ($tenant) {
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

        try {
            // Закрываем все соединения с БД
            DB::statement("SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = ? AND pid <> pg_backend_pid()", [$database]);

            // Удаляем БД через прямое подключение (без транзакции)
            $pdo = new \PDO(
                "pgsql:host=" . env('DB_HOST', '127.0.0.1') . ";port=" . env('DB_PORT', '5432'),
                env('DB_USERNAME', 'postgres'),
                env('DB_PASSWORD', '')
            );

            $pdo->exec("DROP DATABASE IF EXISTS \"{$database}\"");

        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            \Log::warning("Не удалось удалить базу данных {$database}: " . $e->getMessage());
        }
    }
}
