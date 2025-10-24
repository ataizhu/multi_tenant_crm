<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CreateTenantCommand extends Command {
    protected $signature = 'tenant:create {name} {domain} {database}';
    protected $description = 'Create a new tenant with database and run migrations';

    public function handle() {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $database = $this->argument('database');

        // Проверяем, не существует ли уже такой тенант
        if (Tenant::where('domain', $domain)->exists()) {
            $this->error("Tenant with domain '{$domain}' already exists!");
            return 1;
        }

        if (Tenant::where('database', $database)->exists()) {
            $this->error("Tenant with database '{$database}' already exists!");
            return 1;
        }

        try {
            // Создаем базу данных
            $this->info("Creating database: {$database}");
            DB::statement("CREATE DATABASE \"{$database}\"");

            // Создаем тенанта
            $this->info("Creating tenant: {$name}");
            $tenant = Tenant::create([
                'name' => $name,
                'domain' => $domain,
                'database' => $database,
                'status' => 'active',
            ]);

            // Настраиваем подключение к новой БД
            Config::set('database.connections.tenant', [
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

            // Выполняем миграции
            $this->info("Running migrations for tenant: {$name}");
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $this->info("✅ Tenant '{$name}' created successfully!");
            $this->info("   Domain: {$domain}");
            $this->info("   Database: {$database}");
            $this->info("   ID: {$tenant->id}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to create tenant: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
