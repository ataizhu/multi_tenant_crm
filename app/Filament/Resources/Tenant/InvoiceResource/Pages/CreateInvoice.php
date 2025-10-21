<?php

namespace App\Filament\Resources\Tenant\InvoiceResource\Pages;

use App\Filament\Resources\Tenant\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord {
    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string {
        return 'Счет успешно создан';
    }
}
