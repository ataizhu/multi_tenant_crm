<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleCrudTest extends TestCase {
    use RefreshDatabase;

    /** @test */
    public function can_create_tenant() {
        $tenantData = [
            'name' => 'Test Company',
            'domain' => 'test-company-' . time(),
            'database' => 'tenant_test_company_' . time(),
            'email' => 'test@company.com',
            'phone' => '+996 555 123 456',
            'address' => 'ул. Тестовая 123',
            'contact_person' => 'Тестовый Админ',
        ];

        $tenant = Tenant::create($tenantData);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals($tenantData['name'], $tenant->name);
        $this->assertEquals($tenantData['domain'], $tenant->domain);
        $this->assertEquals($tenantData['email'], $tenant->email);
        $this->assertDatabaseHas('tenants', [
            'name' => $tenantData['name'],
            'domain' => $tenantData['domain'],
        ]);
    }

    /** @test */
    public function can_read_tenant() {
        $tenant = Tenant::factory()->create([
            'name' => 'Read Test Company',
            'domain' => 'read-test-company-' . time(),
        ]);

        $foundTenant = Tenant::find($tenant->id);

        $this->assertInstanceOf(Tenant::class, $foundTenant);
        $this->assertEquals($tenant->name, $foundTenant->name);
        $this->assertEquals($tenant->domain, $foundTenant->domain);
    }

    /** @test */
    public function can_update_tenant() {
        $tenant = Tenant::factory()->create([
            'name' => 'Original Name',
            'domain' => 'original-domain-' . time(),
        ]);

        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@company.com',
            'phone' => '+996 777 888 999',
        ];

        $tenant->update($updatedData);

        $this->assertEquals($updatedData['name'], $tenant->name);
        $this->assertEquals($updatedData['email'], $tenant->email);
        $this->assertEquals($updatedData['phone'], $tenant->phone);
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => $updatedData['name'],
        ]);
    }

    /** @test */
    public function can_delete_tenant() {
        $tenant = Tenant::factory()->create([
            'name' => 'Delete Test Company',
            'domain' => 'delete-test-company-' . time(),
        ]);

        $tenantId = $tenant->id;
        $tenant->delete();

        $this->assertDatabaseMissing('tenants', ['id' => $tenantId]);
    }

    /** @test */
    public function can_list_tenants() {
        Tenant::factory()->count(3)->create();

        $tenants = Tenant::all();

        $this->assertCount(3, $tenants);
        $this->assertInstanceOf(Tenant::class, $tenants->first());
    }

    /** @test */
    public function can_search_tenants_by_name() {
        Tenant::factory()->create(['name' => 'Alpha Company']);
        Tenant::factory()->create(['name' => 'Beta Company']);
        Tenant::factory()->create(['name' => 'Gamma Company']);

        $tenants = Tenant::where('name', 'LIKE', '%Alpha%')->get();

        $this->assertCount(1, $tenants);
        $this->assertEquals('Alpha Company', $tenants->first()->name);
    }

    /** @test */
    public function tenant_validation_works() {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Пытаемся создать тенанта без обязательных полей
        Tenant::create([
            'name' => '', // Пустое имя
            'domain' => 'test-domain-' . time(),
        ]);
    }

    /** @test */
    public function can_create_user() {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => bcrypt('password'),
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
    }

    /** @test */
    public function can_read_user() {
        $user = User::factory()->create([
            'name' => 'Read Test User',
            'email' => 'read@test.com',
        ]);

        $foundUser = User::find($user->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->name, $foundUser->name);
        $this->assertEquals($user->email, $foundUser->email);
    }

    /** @test */
    public function can_update_user() {
        $user = User::factory()->create([
            'name' => 'Original User Name',
            'email' => 'original@user.com',
        ]);

        $updatedData = [
            'name' => 'Updated User Name',
            'email' => 'updated@user.com',
        ];

        $user->update($updatedData);

        $this->assertEquals($updatedData['name'], $user->name);
        $this->assertEquals($updatedData['email'], $user->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updatedData['name'],
        ]);
    }

    /** @test */
    public function can_delete_user() {
        $user = User::factory()->create([
            'name' => 'Delete Test User',
            'email' => 'delete@test.com',
        ]);

        $userId = $user->id;
        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /** @test */
    public function can_list_users() {
        User::factory()->count(3)->create();

        $users = User::all();

        $this->assertCount(3, $users);
        $this->assertInstanceOf(User::class, $users->first());
    }

    /** @test */
    public function user_validation_works() {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Пытаемся создать пользователя без обязательных полей
        User::create([
            'name' => '', // Пустое имя
            'email' => 'test@user.com',
        ]);
    }

    /** @test */
    public function tenant_has_unique_domain() {
        $domain = 'unique-domain-' . time();

        // Создаем первого тенанта
        Tenant::factory()->create(['domain' => $domain]);

        // Пытаемся создать второго тенанта с тем же доменом
        $this->expectException(\Illuminate\Database\QueryException::class);

        Tenant::factory()->create(['domain' => $domain]);
    }

    /** @test */
    public function tenant_has_unique_database() {
        $database = 'unique_database_' . time();

        // Создаем первого тенанта
        Tenant::factory()->create(['database' => $database]);

        // Пытаемся создать второго тенанта с той же базой данных
        $this->expectException(\Illuminate\Database\QueryException::class);

        Tenant::factory()->create(['database' => $database]);
    }

    /** @test */
    public function user_has_unique_email() {
        $email = 'unique@test.com';

        // Создаем первого пользователя
        User::factory()->create(['email' => $email]);

        // Пытаемся создать второго пользователя с тем же email
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => $email]);
    }

    /** @test */
    public function tenant_can_have_settings() {
        $settings = [
            'theme' => 'dark',
            'language' => 'ru',
            'notifications' => [
                'email' => true,
                'sms' => false,
            ],
        ];

        $tenant = Tenant::factory()->create([
            'name' => 'Settings Test Company',
            'domain' => 'settings-test-' . time(),
            'settings' => $settings,
        ]);

        $this->assertEquals($settings, $tenant->settings);
        $this->assertEquals('dark', $tenant->settings['theme']);
        $this->assertEquals('ru', $tenant->settings['language']);
        $this->assertTrue($tenant->settings['notifications']['email']);
        $this->assertFalse($tenant->settings['notifications']['sms']);
    }

    /** @test */
    public function tenant_can_be_marked_as_deleted() {
        $tenant = Tenant::factory()->create([
            'name' => 'Delete Test Company',
            'domain' => 'delete-test-' . time(),
            'deleted' => false,
        ]);

        $this->assertFalse($tenant->deleted);
        $this->assertFalse($tenant->isDeleted());

        $tenant->markAsDeleted();

        $tenant->refresh();
        $this->assertTrue($tenant->deleted);
        $this->assertTrue($tenant->isDeleted());
    }

    /** @test */
    public function tenant_can_be_restored() {
        $tenant = Tenant::factory()->create([
            'name' => 'Restore Test Company',
            'domain' => 'restore-test-' . time(),
            'deleted' => true,
        ]);

        $this->assertTrue($tenant->deleted);
        $this->assertTrue($tenant->isDeleted());

        $tenant->restore();

        $tenant->refresh();
        $this->assertFalse($tenant->deleted);
        $this->assertFalse($tenant->isDeleted());
    }

    /** @test */
    public function tenant_scopes_work() {
        // Создаем активных тенантов
        $activeTenant1 = Tenant::factory()->create(['deleted' => false]);
        $activeTenant2 = Tenant::factory()->create(['deleted' => false]);

        // Создаем удаленных тенантов
        $deletedTenant1 = Tenant::factory()->create(['deleted' => true]);
        $deletedTenant2 = Tenant::factory()->create(['deleted' => true]);

        // Проверяем scope для активных тенантов
        $activeTenants = Tenant::active()->get();
        $this->assertCount(2, $activeTenants);
        foreach ($activeTenants as $tenant) {
            $this->assertFalse($tenant->deleted);
        }

        // Проверяем scope для удаленных тенантов
        $deletedTenants = Tenant::where('deleted', true)->get();
        $this->assertCount(2, $deletedTenants);
        foreach ($deletedTenants as $tenant) {
            $this->assertTrue($tenant->deleted);
        }
    }
}
