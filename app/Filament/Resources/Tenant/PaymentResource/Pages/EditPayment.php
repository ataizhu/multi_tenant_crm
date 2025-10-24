<?php

namespace App\Filament\Resources\Tenant\PaymentResource\Pages;

use App\Filament\Resources\Tenant\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord {
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index', ['tenant' => request()->get('tenant')]);
    }

    protected function getSavedNotificationTitle(): ?string {
        return 'Платеж успешно обновлен';
    }
}
