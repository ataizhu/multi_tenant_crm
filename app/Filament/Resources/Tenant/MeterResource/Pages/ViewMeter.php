<?php

namespace App\Filament\Resources\Tenant\MeterResource\Pages;

use App\Filament\Resources\Pages\BaseViewRecord;
use App\Filament\Resources\Tenant\MeterResource;
use App\Filament\Traits\HasRelationTabs;
use App\Models\Meter;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewMeter extends BaseViewRecord {
    use HasRelationTabs;

    protected static string $resource = MeterResource::class;

    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Tabs::make('Details')
                ->tabs([
                    $this->makeMainInfoTab(),
                    $this->makeReadingsTab(),
                    $this->makeHistoryTab(),
                ])
        ]);
    }

    protected function makeMainInfoTab(): Tabs\Tab {
        return Tabs\Tab::make(__('common.main_info'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                $this->makeInfoSection(__('meters.sections.basic'), [
                    TextEntry::make('number')
                        ->label(__('meters.labels.number')),
                    TextEntry::make('type')
                        ->label(__('meters.labels.type'))
                        ->badge()
                        ->color(fn($state) => Meter::getTypeColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Meter::getTypes()[$state] ?? $state),
                    TextEntry::make('model')
                        ->label(__('meters.labels.model')),
                    TextEntry::make('manufacturer')
                        ->label(__('meters.labels.manufacturer')),
                    TextEntry::make('status')
                        ->label(__('meters.labels.status'))
                        ->badge()
                        ->color(fn($state) => Meter::getStatusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Meter::getStatuses()[$state] ?? $state),
                    TextEntry::make('last_reading')
                        ->label(__('meters.labels.last_reading'))
                        ->numeric(),
                    TextEntry::make('last_reading_date')
                        ->label(__('meters.labels.last_reading_date'))
                        ->date('d.m.Y'),
                ]),
                $this->makeInfoSection(__('meters.sections.technical'), [
                    TextEntry::make('installation_date')
                        ->label(__('meters.labels.installation_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('verification_date')
                        ->label(__('meters.labels.verification_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('next_verification_date')
                        ->label(__('meters.labels.next_verification_date'))
                        ->date('d.m.Y'),
                ]),
            ]);
    }

    protected function makeReadingsTab(): Tabs\Tab {
        return $this->makeRelationTab(
            __('meters.tabs.readings'),
            'heroicon-o-chart-bar',
            'readings',
            [
                TextEntry::make('reading')
                    ->label(__('meter_readings.labels.reading'))
                    ->numeric(),
                TextEntry::make('reading_date')
                    ->label(__('meter_readings.labels.date'))
                    ->date('d.m.Y'),
                TextEntry::make('notes')
                    ->label(__('meter_readings.labels.notes')),
            ]
        );
    }

    protected function makeHistoryTab(): Tabs\Tab {
        return Tabs\Tab::make(__('meters.tabs.history'))
            ->icon('heroicon-o-clock')
            ->schema([
                TextEntry::make('created_at')
                    ->label(__('common.created_at'))
                    ->dateTime('d.m.Y H:i'),
                TextEntry::make('updated_at')
                    ->label(__('common.updated_at'))
                    ->dateTime('d.m.Y H:i'),
            ]);
    }
}
