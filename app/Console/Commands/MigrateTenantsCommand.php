<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateTenantsCommand extends Command {
    protected $signature = 'tenants:migrate {--fresh : Drop all tables and re-run all migrations}';
    protected $description = 'Run migrations for all tenant databases';

    public function handle() {
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->info('No active tenants found.');
            return;
        }

        $this->info("Found {$tenants->count()} active tenants.");

        $fresh = $this->option('fresh');

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->name} (ID: {$tenant->id})");

            try {
                $this->migrateTenant($tenant, $fresh);
                $this->info("✅ Migrations completed for tenant: {$tenant->name}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to migrate tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('All tenant migrations completed!');
    }

    private function migrateTenant(Tenant $tenant, bool $fresh = false) {
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

        // Выполняем миграции
        if ($fresh) {
            $this->call('migrate:fresh', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        } else {
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        }
    }
}
