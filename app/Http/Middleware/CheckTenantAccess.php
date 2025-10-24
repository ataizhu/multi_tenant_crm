<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $tenant = $request->get('tenant');

        if (!$tenant) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Тенант не найден');
        }

        // Проверяем, что пользователь имеет доступ к этому тенанту
        $user = auth()->guard('tenant')->user();

        if (!$user) {
            // Если есть параметр auto_login или сессия автовхода, пропускаем проверку
            if (($request->has('auto_login') && $request->get('auto_login') === 'true') ||
                session('tenant_auto_logged_in')) {
                return $next($request);
            }

            return redirect()->route('tenant.login', ['tenant' => $tenant->domain])
                ->with('error', 'Необходима авторизация');
        }

        // Если это пользователь тенанта, проверяем его доступ
        if ($user instanceof \App\Models\TenantUser) {
            if ($user->tenant_id !== $tenant->id) {
                return redirect()->route('filament.admin.pages.dashboard')
                    ->with('error', 'Нет доступа к этому тенанту');
            }

            if (!$user->isActive()) {
                return redirect()->route('tenant.login', ['tenant' => $tenant->domain])
                    ->with('error', 'Аккаунт заблокирован');
            }
        }

        return $next($request);
    }
}
