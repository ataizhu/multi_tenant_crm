<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\TenantDatabaseMiddleware;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class TenantDatabaseMiddlewareTest extends TestCase {
    use RefreshDatabase;

    protected $middleware;
    protected $request;

    protected function setUp(): void {
        parent::setUp();
        $this->middleware = new TenantDatabaseMiddleware();
        $this->request = Request::create('/test');
    }

    /** @test */
    public function it_switches_to_tenant_database_from_url_parameter() {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'domain' => 'test-company',
            'database' => 'tenant_test_company'
        ]);

        $request = Request::create('/test', 'GET', ['tenant' => $tenant->id]);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Проверяем, что конфигурация базы данных обновлена
        $this->assertEquals('tenant_test_company', config('database.connections.tenant.database'));

        // Проверяем, что тенант сохранен в сессии
        $this->assertEquals($tenant->id, session('current_tenant')->id);
    }

    /** @test */
    public function it_switches_to_tenant_database_from_subdomain() {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'domain' => 'test-company',
            'database' => 'tenant_test_company'
        ]);

        $request = Request::create('/test');
        $request->headers->set('HOST', 'test-company.localhost');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals('tenant_test_company', config('database.connections.tenant.database'));
    }

    /** @test */
    public function it_uses_session_tenant_if_available() {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'domain' => 'test-company',
            'database' => 'tenant_test_company'
        ]);

        session(['current_tenant' => $tenant]);

        $request = Request::create('/test');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertEquals('tenant_test_company', config('database.connections.tenant.database'));
    }

    /** @test */
    public function it_handles_invalid_tenant_id() {
        $request = Request::create('/test', 'GET', ['tenant' => 99999]);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Проверяем, что конфигурация не изменилась
        $this->assertNotEquals('tenant_test_company', config('database.connections.tenant.database'));
    }

    /** @test */
    public function it_handles_deleted_tenant() {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'domain' => 'test-company',
            'database' => 'tenant_test_company',
            'deleted' => true
        ]);

        $request = Request::create('/test', 'GET', ['tenant' => $tenant->id]);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Проверяем, что конфигурация не изменилась для удаленного тенанта
        $this->assertNotEquals('tenant_test_company', config('database.connections.tenant.database'));
    }

    /** @test */
    public function it_prioritizes_url_parameter_over_subdomain() {
        $tenant1 = Tenant::factory()->create([
            'name' => 'URL Tenant',
            'domain' => 'url-tenant',
            'database' => 'tenant_url'
        ]);

        $tenant2 = Tenant::factory()->create([
            'name' => 'Subdomain Tenant',
            'domain' => 'subdomain-tenant',
            'database' => 'tenant_subdomain'
        ]);

        $request = Request::create('/test', 'GET', ['tenant' => $tenant1->id]);
        $request->headers->set('HOST', 'subdomain-tenant.localhost');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Должен использовать тенанта из URL параметра
        $this->assertEquals('tenant_url', config('database.connections.tenant.database'));
        $this->assertEquals($tenant1->id, session('current_tenant')->id);
    }

    /** @test */
    public function it_handles_localhost_subdomain() {
        $request = Request::create('/test');
        $request->headers->set('HOST', 'localhost');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // localhost не должен обрабатываться как поддомен
        $this->assertNotEquals('tenant_test_company', config('database.connections.tenant.database'));
    }

    /** @test */
    public function it_sets_tenant_in_request_attributes() {
        $tenant = Tenant::factory()->create([
            'name' => 'Test Company',
            'domain' => 'test-company',
            'database' => 'tenant_test_company'
        ]);

        $request = Request::create('/test', 'GET', ['tenant' => $tenant->id]);

        $response = $this->middleware->handle($request, function ($req) use ($tenant) {
            $this->assertEquals($tenant->id, $req->attributes->get('tenant')->id);
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_handles_missing_tenant_gracefully() {
        $request = Request::create('/test');

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Проверяем, что middleware не сломал запрос
        $this->assertNull(session('current_tenant'));
    }
}