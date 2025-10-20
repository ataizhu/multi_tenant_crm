<?php

namespace App\Filament\Resources\TenantTrashResource\Pages;

use App\Filament\Resources\TenantTrashResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantTrash extends EditRecord
{
    protected static string $resource = TenantTrashResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
