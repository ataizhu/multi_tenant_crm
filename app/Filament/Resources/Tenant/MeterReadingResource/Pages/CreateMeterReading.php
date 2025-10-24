<?php

namespace App\Filament\Resources\Tenant\MeterReadingResource\Pages;

use App\Filament\Resources\Tenant\MeterReadingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeterReading extends CreateRecord {
    protected static string $resource = MeterReadingResource::class;

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getCreatedNotificationTitle(): ?string {
        return 'Показание счетчика успешно добавлено';
    }
}
