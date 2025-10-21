<?php

namespace App\Filament\Resources\Tenant\ServiceResource\Pages;

use App\Filament\Resources\Tenant\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord {
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string {
        return 'Услуга успешно обновлена';
    }
}
