<?php

namespace App\Filament\Resources\Tenant\ServiceResource\Pages;

use App\Filament\Resources\Tenant\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord {
    protected static string $resource = ServiceResource::class;

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string {
        return 'Услуга успешно создана';
    }
}
