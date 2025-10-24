<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CheckTenantsMigrationsCommand extends Command {
    protected $signature = 'tenants:migrate-status';
    protected $description = 'Check migration status for all tenant databases';

    public function handle() {
        $tenants = Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->info('No active tenants found.');
            return;
        }

        $this->info("Checking migration status for {$tenants->count()} active tenants...\n");

        $headers = ['Tenant ID', 'Name', 'Database', 'Status', 'Pending Migrations'];
        $rows = [];

        foreach ($tenants as $tenant) {
            try {
                $status = $this->checkTenantMigrations($tenant);
                $rows[] = [
                    $tenant->id,
                    $tenant->name,
                    $tenant->database,
                    $status['status'],
                    $status['pending_count']
                ];
            } catch (\Exception $e) {
                $rows[] = [
                    $tenant->id,
                    $tenant->name,
                    $tenant->database,
                    '❌ Error',
                    $e->getMessage()
                ];
            }
        }

        $this->table($headers, $rows);
    }

    private function checkTenantMigrations(Tenant $tenant): array {
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

        try {
            // Проверяем статус миграций
            $pendingMigrations = $this->call('migrate:status', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
            ]);

            // Получаем список таблиц
            $tables = DB::connection('tenant')->select("
                SELECT table_name 
                FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_type = 'BASE TABLE'
            ");

            $tableCount = count($tables);
            $expectedTables = ['migrations', 'subscribers', 'meters', 'meter_readings', 'invoices', 'payments', 'services'];
            $hasAllTables = count(array_intersect($expectedTables, array_column($tables, 'table_name'))) === count($expectedTables);

            if ($hasAllTables) {
                return [
                    'status' => '✅ OK',
                    'pending_count' => '0'
                ];
            } else {
                return [
                    'status' => '⚠️ Incomplete',
                    'pending_count' => count($expectedTables) - $tableCount
                ];
            }

        } catch (\Exception $e) {
            return [
                'status' => '❌ Error',
                'pending_count' => $e->getMessage()
            ];
        }
    }
}
