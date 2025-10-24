<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class MigrateTenantsAfterMainCommand extends Command {
    protected $signature = 'migrate:tenants-after-main';
    protected $description = 'Run tenant migrations after main migrations (called automatically)';

    public function handle() {
        $this->info('Running migrations for all tenants...');

        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->info('No active tenants found.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->name}");

            try {
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
                $this->call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                $this->info("✅ Completed: {$tenant->name}");

            } catch (\Exception $e) {
                $this->error("❌ Failed: {$tenant->name} - " . $e->getMessage());
            }
        }

        $this->info('All tenant migrations completed!');
    }
}
