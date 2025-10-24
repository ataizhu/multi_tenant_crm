<?php

namespace App\Filament\Traits;

trait TenantAwareBreadcrumbs {
    /**
     * Получить ID текущего тенанта из запроса или сессии
     */
    protected function getCurrentTenantId(): int {
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
     * Создать URL с параметром tenant
     */
    protected function createTenantUrl(string $path): string {
        $tenantId = $this->getCurrentTenantId();
        $separator = strpos($path, '?') !== false ? '&' : '?';
        return $path . $separator . 'tenant=' . $tenantId;
    }

    /**
     * Получить breadcrumbs с правильным контекстом тенанта
     */
    public function getBreadcrumbs(): array {
        $tenantId = $this->getCurrentTenantId();
        $baseUrl = '/tenant-crm/tenant';

        // Определяем текущий ресурс и создаем соответствующие breadcrumbs
        $resourceName = $this->getResourceName();

        return [
            $this->createTenantUrl($baseUrl . '/subscribers') => 'Абоненты',
        ];
    }

    /**
     * Получить название текущего ресурса
     */
    protected function getResourceName(): string {
        $className = get_class($this);
        if (strpos($className, 'Subscriber') !== false)
            return 'subscribers';
        if (strpos($className, 'Invoice') !== false)
            return 'invoices';
        if (strpos($className, 'Payment') !== false)
            return 'payments';
        if (strpos($className, 'Meter') !== false)
            return 'meters';
        if (strpos($className, 'Service') !== false)
            return 'services';
        return 'subscribers'; // по умолчанию
    }
}
