<?php

namespace App\Filament\Resources\Tenant\ServiceResource\Pages;

use App\Filament\Resources\Pages\BaseViewRecord;
use App\Filament\Resources\Tenant\ServiceResource;
use App\Filament\Traits\HasRelationTabs;
use App\Models\Service;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewService extends BaseViewRecord {
    use HasRelationTabs;

    protected static string $resource = ServiceResource::class;

    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Tabs::make('Details')
                ->tabs([
                    $this->makeMainInfoTab(),
                    $this->makeSubscribersTab(),
                    $this->makeInvoicesTab(),
                ])
        ]);
    }

    protected function makeMainInfoTab(): Tabs\Tab {
        return Tabs\Tab::make(__('common.main_info'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                $this->makeInfoSection(__('services.sections.basic'), [
                    TextEntry::make('name')
                        ->label(__('services.labels.name')),
                    TextEntry::make('type')
                        ->label(__('services.labels.type'))
                        ->badge()
                        ->color(fn($state) => Service::getTypeColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Service::getTypes()[$state] ?? $state),
                    TextEntry::make('description')
                        ->label(__('services.labels.description'))
                        ->columnSpanFull(),
                ]),
                $this->makeInfoSection(__('services.sections.pricing'), [
                    TextEntry::make('price')
                        ->label(__('services.labels.price'))
                        ->money('RUB'),
                    TextEntry::make('unit')
                        ->label(__('services.labels.unit')),
                    TextEntry::make('billing_cycle')
                        ->label(__('services.labels.billing_cycle')),
                ]),
                $this->makeInfoSection(__('services.sections.schedule'), [
                    TextEntry::make('start_date')
                        ->label(__('services.labels.start_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('end_date')
                        ->label(__('services.labels.end_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('is_active')
                        ->label(__('services.labels.status'))
                        ->badge()
                        ->color(fn($state) => $state ? 'success' : 'gray')
                        ->formatStateUsing(fn($state) => $state ? __('common.active') : __('common.inactive')),
                ]),
            ]);
    }

    protected function makeSubscribersTab(): Tabs\Tab {
        return Tabs\Tab::make(__('services.tabs.subscribers'))
            ->icon('heroicon-o-users')
            ->schema([
                TextEntry::make('subscribers_count')
                    ->label(__('services.subscribers_count'))
                    ->state(fn() => $this->record->subscribers()->count()),
            ]);
    }

    protected function makeInvoicesTab(): Tabs\Tab {
        return Tabs\Tab::make(__('services.tabs.invoices'))
            ->icon('heroicon-o-document-text')
            ->schema([
                TextEntry::make('invoices_count')
                    ->label(__('services.invoices_count'))
                    ->state(fn() => $this->record->invoices()->count()),
            ]);
    }
}
