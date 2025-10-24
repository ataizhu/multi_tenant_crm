<?php

namespace App\Http\Middleware;

use App\Models\TenantUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoLoginTenantAdmin {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $tenant = $request->get('tenant');

        // Проверяем, есть ли параметр auto_login и пользователь не авторизован
        if ($request->has('auto_login') && $request->get('auto_login') === 'true' && !Auth::guard('tenant')->check()) {

            if ($tenant && is_object($tenant)) {
                try {
                    // Настраиваем подключение к базе данных тенанта
                    $config = config('database.connections.tenant');
                    $config['database'] = $tenant->database;
                    config(['database.connections.tenant' => $config]);

                    // Ищем администратора тенанта в правильной базе данных
                    $adminUser = TenantUser::on('tenant')->where('tenant_id', $tenant->id)
                        ->where('is_admin', true)
                        ->where('is_active', true)
                        ->first();

                    if ($adminUser) {
                        // Автоматически входим под администратором с remember
                        Auth::guard('tenant')->login($adminUser, true);

                        // Сохраняем информацию о тенанте в сессии для последующих запросов
                        session([
                            'tenant_auto_logged_in' => true,
                            'tenant_id' => $tenant->id,
                            'tenant_domain' => $tenant->domain,
                            'tenant_user_id' => $adminUser->id
                        ]);

                        Log::info("Auto-login successful for tenant {$tenant->id} as admin {$adminUser->email}");

                        // Удаляем параметр auto_login из URL для безопасности
                        $request->query->remove('auto_login');
                    } else {
                        Log::warning("No admin user found for tenant {$tenant->id}");
                    }
                } catch (\Exception $e) {
                    Log::error("Auto-login failed for tenant {$tenant->id}: " . $e->getMessage());
                }
            } else {
                Log::warning("Auto-login attempted but tenant not found or invalid");
            }
        }

        return $next($request);
    }
}
