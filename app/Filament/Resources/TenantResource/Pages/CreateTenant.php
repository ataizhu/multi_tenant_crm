<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Services\TenantService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTenant extends CreateRecord {
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array {
        // Генерируем имя БД если не указано
        if (empty($data['database'])) {
            $data['database'] = 'tenant_' . strtolower(str_replace([' ', '-'], '_', $data['name']));
        }

        // Добавляем суффикс к домену если не указан
        if (!empty($data['domain']) && !str_contains($data['domain'], '.')) {
            $data['domain'] = $data['domain'] . '.zhkh.local';
        }

        return $data;
    }

    protected function afterCreate(): void {
        try {
            $tenantService = app(TenantService::class);
            $tenantService->createTenantDatabase($this->record);
            $tenantService->runTenantMigrations($this->record);

            Notification::make()
                ->title('Клиент создан успешно')
                ->body('База данных и структура созданы')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка при создании клиента')
                ->body($e->getMessage())
                ->danger()
                ->send();

            // Удаляем созданную запись если не удалось создать БД
            $this->record->delete();
        }
    }
}
