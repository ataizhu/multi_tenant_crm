<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantLocale {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $tenant = $request->get('tenant');
        $user = auth()->guard('tenant')->user();

        // Приоритет: пользователь -> тенант -> дефолт
        $locale = 'ru'; // дефолт

        if ($user instanceof \App\Models\TenantUser) {
            $locale = $user->getLocale();
        } elseif ($tenant && isset($tenant->locale)) {
            $locale = $tenant->locale;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
