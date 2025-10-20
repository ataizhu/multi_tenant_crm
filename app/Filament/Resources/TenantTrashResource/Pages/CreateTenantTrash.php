<?php

namespace App\Filament\Resources\TenantTrashResource\Pages;

use App\Filament\Resources\TenantTrashResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantTrash extends CreateRecord
{
    protected static string $resource = TenantTrashResource::class;
}
