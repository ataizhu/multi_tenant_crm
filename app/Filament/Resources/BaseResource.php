<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;

abstract class BaseResource extends Resource {
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = null;

    public static function getNavigationLabel(): string {
        return __(static::getResourceName() . '.navigation_label');
    }

    public static function getPluralModelLabel(): string {
        return __(static::getResourceName() . '.plural_label');
    }

    public static function getModelLabel(): string {
        return __(static::getResourceName() . '.model_label');
    }

    abstract protected static function getResourceName(): string;

    public static function getPages(): array {
        return [
            'index' => static::getListPage(),
            'create' => static::getCreatePage(),
            'view' => static::getViewPage(),
            'edit' => static::getEditPage(),
        ];
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
}
