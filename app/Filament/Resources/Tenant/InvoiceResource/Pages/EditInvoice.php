<?php

namespace App\Filament\Resources\Tenant\InvoiceResource\Pages;

use App\Filament\Resources\Tenant\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord {
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getSavedNotificationTitle(): ?string {
        return 'Счет успешно обновлен';
    }
}
