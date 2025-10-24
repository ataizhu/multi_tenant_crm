<?php

namespace App\Filament\Components;

use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\Str;

class RelationListEntry {
    public static function make(string $relationship, array $schema): RepeatableEntry {
        return RepeatableEntry::make($relationship)
            ->label('')
            ->schema($schema)
            ->columns(6)
            ->extraAttributes([
                'class' => 'horizontal-list-item',
                'data-type' => Str::singular($relationship)
            ]);
    }
}
