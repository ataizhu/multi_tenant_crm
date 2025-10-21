<?php

namespace App\Filament\Resources\Tenant\MeterReadingResource\Pages;

use App\Filament\Resources\Tenant\MeterReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeterReadings extends ListRecords {
    protected static string $resource = MeterReadingResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make()
                ->label('Добавить показание')
                ->icon('heroicon-o-plus'),
        ];
    }
}
