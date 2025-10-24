<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\Pages;

use App\Filament\Resources\Tenant\SubscriberResource;
use App\Filament\Traits\TenantAwareBreadcrumbs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscribers extends ListRecords {
    use TenantAwareBreadcrumbs;

    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make()
                ->label('Добавить абонента')
                ->icon('heroicon-o-plus'),
        ];
    }

    // Настройки для полного экрана
    protected function getHeaderWidgets(): array {
        return [
            \App\Filament\Tenant\Widgets\SubscriberStatsWidget::class,
        ];
    }
}