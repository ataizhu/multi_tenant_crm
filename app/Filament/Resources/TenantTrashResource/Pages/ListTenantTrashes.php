<?php

namespace App\Filament\Resources\TenantTrashResource\Pages;

use App\Filament\Resources\TenantTrashResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantTrashes extends ListRecords
{
    protected static string $resource = TenantTrashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
