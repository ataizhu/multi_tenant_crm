<?php

namespace App\Filament\Resources\Tenant\MeterReadingResource\Pages;

use App\Filament\Resources\Tenant\MeterReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeterReading extends EditRecord {
    protected static string $resource = MeterReadingResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getSavedNotificationTitle(): ?string {
        return 'Показание счетчика успешно обновлено';
    }
}
