<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantUser extends EditRecord
{
    protected static string $resource = TenantUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
