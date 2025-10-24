<?php

namespace App\Filament\Resources\Tenant\MeterResource\Pages;

use App\Filament\Resources\Tenant\MeterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeter extends CreateRecord {
    protected static string $resource = MeterResource::class;

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getCreatedNotificationTitle(): ?string {
        return 'Счетчик успешно создан';
    }
}
