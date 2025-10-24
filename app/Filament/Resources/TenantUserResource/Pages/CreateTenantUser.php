<?php

namespace App\Filament\Resources\TenantUserResource\Pages;

use App\Filament\Resources\TenantUserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantUser extends CreateRecord
{
    protected static string $resource = TenantUserResource::class;
}
