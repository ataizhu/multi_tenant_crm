<?php

namespace App\Filament\Traits;

use App\Filament\Components\RelationListEntry;
use Filament\Infolists\Components\Tabs\Tab;

trait HasRelationTabs {
    protected function makeRelationTab(
        string $title,
        string $icon,
        string $relation,
        array $schema
    ): Tab {
        return Tab::make($title)
            ->icon($icon)
            ->badge(fn() => $this->record->{$relation}()->count())
            ->schema([
                RelationListEntry::make($relation, $schema)
            ]);
    }
}
