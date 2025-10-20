<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Services\TenantService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTenant extends EditRecord {
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\Action::make('soft_delete')
                ->label('В корзину')
                ->color('warning')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('Переместить в корзину')
                ->modalDescription('Тенант будет перемещен в корзину и может быть восстановлен позже.')
                ->form([
                    Forms\Components\Textarea::make('deletion_reason')
                        ->label('Причина удаления')
                        ->placeholder('Укажите причину удаления (необязательно)')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    try {
                        $tenantService = app(TenantService::class);
                        $tenantService->softDeleteTenant($this->record, $data['deletion_reason'] ?? null);

                        Notification::make()
                            ->title('Тенант перемещен в корзину')
                            ->success()
                            ->send();

                        $this->redirect('/admin/tenants');
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Ошибка при удалении клиента')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
