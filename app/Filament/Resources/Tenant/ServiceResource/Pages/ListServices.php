<?php

namespace App\Filament\Resources\Tenant\ServiceResource\Pages;

use App\Filament\Resources\Tenant\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords {
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make()
                ->label('Добавить услугу')
                ->icon('heroicon-o-plus'),
        ];
    }
}
