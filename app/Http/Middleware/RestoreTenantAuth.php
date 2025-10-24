<?php

namespace App\Http\Middleware;

use App\Models\TenantUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestoreTenantAuth {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $tenant = $request->get('tenant');

        // Если пользователь не авторизован, но есть информация о автовходе в сессии
        if (!$tenant || !Auth::guard('tenant')->check()) {
            if (session('tenant_auto_logged_in') && session('tenant_id') && session('tenant_user_id')) {
                try {
                    // Настраиваем подключение к базе данных тенанта
                    $config = config('database.connections.tenant');
                    $config['database'] = \App\Models\Tenant::find(session('tenant_id'))->database ?? null;

                    if ($config['database']) {
                        config(['database.connections.tenant' => $config]);

                        // Восстанавливаем пользователя из сессии
                        $adminUser = TenantUser::on('tenant')->find(session('tenant_user_id'));

                        if ($adminUser && $adminUser->isActive()) {
                            Auth::guard('tenant')->login($adminUser, true);
                            Log::info("Restored tenant auth for user {$adminUser->email}");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to restore tenant auth: " . $e->getMessage());
                }
            }
        }

        return $next($request);
    }
}
