<?php

namespace App\Filament\Resources;

use App\Models\Tenant;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

abstract class BaseTenantResource extends BaseResource {
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Tenant Management';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder {
        return parent::getEloquentQuery()
            ->where('tenant_id', request()->get('tenant')?->id);
    }

    protected static function getListPage(): string {
        return ListRecords::class;
    }

    protected static function getCreatePage(): string {
        return CreateRecord::class;
    }

    protected static function getViewPage(): string {
        return ViewRecord::class;
    }

    protected static function getEditPage(): string {
        return EditRecord::class;
    }

    protected static function mutateFormDataBeforeCreate(array $data): array {
        $data['tenant_id'] = request()->get('tenant')?->id;
        return $data;
    }
}
