<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\Pages;

use App\Filament\Resources\Tenant\SubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSubscriber extends EditRecord {
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Абонент удален')
                        ->body('Абонент успешно удален из системы.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification {
        return Notification::make()
            ->success()
            ->title('Абонент обновлен')
            ->body('Данные абонента успешно сохранены.');
    }

    protected function getRedirectUrl(): string {
        return $this->getResource()::getUrl('index');
    }
}
