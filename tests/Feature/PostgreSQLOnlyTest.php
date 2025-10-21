<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class PostgreSQLOnlyTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();

        // Принудительно устанавливаем PostgreSQL для этого теста
        config(['database.default' => 'pgsql']);
        config(['database.connections.pgsql.database' => 'central_crm']);
    }

    public function test_can_connect_to_postgresql() {
        // Проверяем, что можем подключиться к PostgreSQL
        $result = DB::select('SELECT version() as version');
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('PostgreSQL', $result[0]->version);
    }

    public function test_can_create_database() {
        $databaseName = 'test_tenant_db_' . time();

        try {
            // Создаем базу данных
            DB::statement("CREATE DATABASE {$databaseName}");

            // Проверяем, что база создана
            $result = DB::select("SELECT 1 FROM pg_database WHERE datname = ?", [$databaseName]);
            $this->assertNotEmpty($result);

            // Закрываем все подключения к базе данных
            if (config('database.connections.test_db')) {
                DB::connection('test_db')->disconnect();
            }

            // Удаляем базу данных
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");

        } catch (\Exception $e) {
            // Очищаем в случае ошибки
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
            throw $e;
        }
    }

    public function test_can_switch_database_connection() {
        $databaseName = 'test_switch_db_' . time();

        try {
            // Создаем базу данных
            DB::statement("CREATE DATABASE {$databaseName}");

            // Переключаемся на новую базу
            config(['database.connections.test_db.database' => $databaseName]);
            config(['database.connections.test_db.driver' => 'pgsql']);
            config(['database.connections.test_db.host' => '127.0.0.1']);
            config(['database.connections.test_db.port' => '5432']);
            config(['database.connections.test_db.username' => 'postgres']);
            config(['database.connections.test_db.password' => '']);

            // Проверяем подключение
            $result = DB::connection('test_db')->select('SELECT current_database() as db_name');
            $this->assertEquals($databaseName, $result[0]->db_name);

            // Закрываем все подключения к базе данных
            if (config('database.connections.test_db')) {
                DB::connection('test_db')->disconnect();
            }

            // Удаляем базу данных
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");

        } catch (\Exception $e) {
            // Очищаем в случае ошибки
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
            throw $e;
        }
    }

    public function test_can_create_tables_in_tenant_database() {
        $databaseName = 'test_tables_db_' . time();

        try {
            // Создаем базу данных
            DB::statement("CREATE DATABASE {$databaseName}");

            // Переключаемся на новую базу
            config(['database.connections.test_db.database' => $databaseName]);
            config(['database.connections.test_db.driver' => 'pgsql']);
            config(['database.connections.test_db.host' => '127.0.0.1']);
            config(['database.connections.test_db.port' => '5432']);
            config(['database.connections.test_db.username' => 'postgres']);
            config(['database.connections.test_db.password' => '']);

            // Создаем таблицу
            DB::connection('test_db')->statement('
                CREATE TABLE test_table (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Вставляем данные
            DB::connection('test_db')->table('test_table')->insert([
                'name' => 'Test Record',
                'created_at' => now(),
            ]);

            // Проверяем данные
            $record = DB::connection('test_db')->table('test_table')->first();
            $this->assertNotNull($record);
            $this->assertEquals('Test Record', $record->name);

            // Закрываем все подключения к базе данных
            if (config('database.connections.test_db')) {
                DB::connection('test_db')->disconnect();
            }

            // Удаляем базу данных
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");

        } catch (\Exception $e) {
            // Очищаем в случае ошибки
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
            throw $e;
        }
    }

    public function test_can_create_tenant_database_and_tables() {
        $databaseName = 'test_tenant_full_' . time();

        try {
            // Создаем базу данных
            DB::statement("CREATE DATABASE {$databaseName}");

            // Переключаемся на новую базу
            config(['database.connections.tenant.database' => $databaseName]);
            config(['database.connections.tenant.driver' => 'pgsql']);
            config(['database.connections.tenant.host' => '127.0.0.1']);
            config(['database.connections.tenant.port' => '5432']);
            config(['database.connections.tenant.username' => 'postgres']);
            config(['database.connections.tenant.password' => '']);

            // Создаем таблицу subscribers
            DB::connection('tenant')->statement('
                CREATE TABLE subscribers (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    phone VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    address TEXT,
                    apartment_number VARCHAR(50),
                    building_number VARCHAR(50),
                    status VARCHAR(50) DEFAULT \'active\',
                    balance DECIMAL(10,2) DEFAULT 0,
                    registration_date TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Создаем таблицу meters
            DB::connection('tenant')->statement('
                CREATE TABLE meters (
                    id SERIAL PRIMARY KEY,
                    subscriber_id INTEGER NOT NULL REFERENCES subscribers(id),
                    number VARCHAR(255) NOT NULL,
                    type VARCHAR(100) NOT NULL,
                    model VARCHAR(255),
                    installation_date TIMESTAMP,
                    last_reading DECIMAL(10,2),
                    last_reading_date TIMESTAMP,
                    status VARCHAR(50) DEFAULT \'active\',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Создаем таблицу invoices
            DB::connection('tenant')->statement('
                CREATE TABLE invoices (
                    id SERIAL PRIMARY KEY,
                    subscriber_id INTEGER NOT NULL REFERENCES subscribers(id),
                    invoice_number VARCHAR(255) NOT NULL,
                    invoice_date TIMESTAMP NOT NULL,
                    due_date TIMESTAMP NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    status VARCHAR(50) DEFAULT \'sent\',
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Создаем таблицу payments
            DB::connection('tenant')->statement('
                CREATE TABLE payments (
                    id SERIAL PRIMARY KEY,
                    subscriber_id INTEGER NOT NULL REFERENCES subscribers(id),
                    invoice_id INTEGER REFERENCES invoices(id),
                    payment_number VARCHAR(255) NOT NULL,
                    amount DECIMAL(10,2) NOT NULL,
                    payment_date TIMESTAMP NOT NULL,
                    payment_method VARCHAR(100) NOT NULL,
                    status VARCHAR(50) DEFAULT \'completed\',
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Вставляем тестовые данные
            $subscriberId = DB::connection('tenant')->table('subscribers')->insertGetId([
                'name' => 'Тестовый Абонент',
                'phone' => '+996 555 999 888',
                'email' => 'test@subscriber.com',
                'address' => 'ул. Тестовая 456',
                'apartment_number' => '101',
                'building_number' => '1',
                'status' => 'active',
                'balance' => 1500.00,
                'registration_date' => now(),
            ]);

            $meterId = DB::connection('tenant')->table('meters')->insertGetId([
                'subscriber_id' => $subscriberId,
                'number' => 'METER-001',
                'type' => 'water',
                'model' => 'Test Model',
                'installation_date' => now(),
                'last_reading' => 1000,
                'last_reading_date' => now(),
                'status' => 'active',
            ]);

            $invoiceId = DB::connection('tenant')->table('invoices')->insertGetId([
                'subscriber_id' => $subscriberId,
                'invoice_number' => 'INV-001',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'amount' => 1500.00,
                'status' => 'sent',
                'description' => 'Тестовый счет',
            ]);

            DB::connection('tenant')->table('payments')->insert([
                'subscriber_id' => $subscriberId,
                'invoice_id' => $invoiceId,
                'payment_number' => 'PAY-001',
                'amount' => 1500.00,
                'payment_date' => now(),
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'description' => 'Оплата тестового счета',
            ]);

            // Проверяем, что все данные созданы
            $this->assertEquals(1, DB::connection('tenant')->table('subscribers')->count());
            $this->assertEquals(1, DB::connection('tenant')->table('meters')->count());
            $this->assertEquals(1, DB::connection('tenant')->table('invoices')->count());
            $this->assertEquals(1, DB::connection('tenant')->table('payments')->count());

            // Проверяем связи
            $subscriber = DB::connection('tenant')->table('subscribers')->first();
            $this->assertEquals('Тестовый Абонент', $subscriber->name);

            $meter = DB::connection('tenant')->table('meters')->first();
            $this->assertEquals($subscriberId, $meter->subscriber_id);
            $this->assertEquals('METER-001', $meter->number);

            $invoice = DB::connection('tenant')->table('invoices')->first();
            $this->assertEquals($subscriberId, $invoice->subscriber_id);
            $this->assertEquals('INV-001', $invoice->invoice_number);

            $payment = DB::connection('tenant')->table('payments')->first();
            $this->assertEquals($subscriberId, $payment->subscriber_id);
            $this->assertEquals($invoiceId, $payment->invoice_id);

            // Закрываем все подключения к базе данных
            DB::connection('tenant')->disconnect();

            // Закрываем все подключения к базе данных
            if (config('database.connections.test_db')) {
                DB::connection('test_db')->disconnect();
            }

            // Удаляем базу данных
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");

        } catch (\Exception $e) {
            // Очищаем в случае ошибки
            DB::statement("DROP DATABASE IF EXISTS {$databaseName}");
            throw $e;
        }
    }
}
