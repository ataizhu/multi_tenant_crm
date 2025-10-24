<?php

namespace App\Filament\Resources\Tenant\PaymentResource\Pages;

use App\Filament\Resources\Tenant\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord {
    protected static string $resource = PaymentResource::class;

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getCreatedNotificationTitle(): ?string {
        return 'Платеж успешно создан';
    }
}
