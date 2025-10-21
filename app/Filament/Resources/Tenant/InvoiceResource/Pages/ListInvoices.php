<?php

namespace App\Filament\Resources\Tenant\InvoiceResource\Pages;

use App\Filament\Resources\Tenant\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords {
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make()
                ->label('Добавить счет')
                ->icon('heroicon-o-plus'),
        ];
    }
}
