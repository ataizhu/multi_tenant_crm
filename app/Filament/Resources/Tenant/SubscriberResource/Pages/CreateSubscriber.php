<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\Pages;

use App\Filament\Resources\Tenant\SubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSubscriber extends CreateRecord {
    protected static string $resource = SubscriberResource::class;

    protected function getCreatedNotification(): ?Notification {
        return Notification::make()
            ->success()
            ->title('Абонент создан')
            ->body('Новый абонент успешно добавлен в систему.');
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
