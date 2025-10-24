<?php

namespace App\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

abstract class BaseViewRecord extends ViewRecord {
    public function getMaxContentWidth(): ?string {
        return 'full';
    }

    protected function getHeaderActions(): array {
        return [
            Actions\EditAction::make()
                ->label(__('common.edit'))
                ->color('primary')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label(__('common.delete'))
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function makeInfoSection(string $title, array $schema): Section {
        return Section::make($title)
            ->schema($schema)
            ->columns(2);
    }

    protected function makeGridFields(array $fields): Grid {
        return Grid::make(2)
            ->schema($fields);
    }
}
