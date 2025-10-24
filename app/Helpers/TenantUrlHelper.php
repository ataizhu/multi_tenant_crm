<?php

namespace App\Helpers;

class TenantUrlHelper {
    /**
     * Создать URL для тенанта с правильным контекстом
     */
    public static function createUrl(string $path, $tenantId = null, bool $autoLogin = false): string {
        if ($tenantId === null) {
            $tenantId = self::getCurrentTenantId();
        } else {
            // Безопасное извлечение ID из объекта или числа
            $tenantId = is_object($tenantId) ? $tenantId->id : (int) $tenantId;
        }

        $separator = strpos($path, '?') !== false ? '&' : '?';
        $url = $path . $separator . 'tenant=' . $tenantId;

        // Добавляем параметр автовхода если нужно
        if ($autoLogin) {
            $url .= '&auto_login=true';
        }

        return $url;
    }

    /**
     * Получить текущий ID тенанта
     */
    public static function getCurrentTenantId(): int {
        // Сначала пробуем получить из URL
        $tenantId = request()->get('tenant');

        // Если нет в URL, пробуем из сессии
        if (!$tenantId && session()->has('current_tenant')) {
            $tenant = session('current_tenant');
            if (is_object($tenant)) {
                $tenantId = $tenant->id;
            } else {
                $tenantId = $tenant;
            }
        }

        // Если все еще нет, пробуем из request attributes
        if (!$tenantId && request()->attributes->has('tenant')) {
            $tenant = request()->attributes->get('tenant');
            if (is_object($tenant)) {
                $tenantId = $tenant->id;
            } else {
                $tenantId = $tenant;
            }
        }

        // Дополнительная проверка типа
        if (is_object($tenantId)) {
            $tenantId = $tenantId->id ?? 9;
        }

        return (int) ($tenantId ?? 9);
    }

    /**
     * Получить текущий объект тенанта
     */
    public static function getCurrentTenant(): ?\App\Models\Tenant {
        $tenantId = self::getCurrentTenantId();
        return \App\Models\Tenant::find($tenantId);
    }
}
