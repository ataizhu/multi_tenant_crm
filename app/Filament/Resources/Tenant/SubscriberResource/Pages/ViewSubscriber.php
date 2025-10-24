<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\Pages;

use App\Filament\Resources\Pages\BaseViewRecord;
use App\Filament\Resources\Tenant\SubscriberResource;
use App\Filament\Traits\HasRelationTabs;
use App\Models\Meter;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscriber;
use Filament\Actions;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewSubscriber extends BaseViewRecord {
    use HasRelationTabs;

    protected static string $resource = SubscriberResource::class;

    public function getMaxContentWidth(): ?string {
        return 'full';
    }

    protected function getFooterWidgets(): array {
        return [];
    }

    protected function getHeaderWidgets(): array {
        return [];
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

    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Tabs::make('Details')
                ->tabs([
                    $this->makeContactTab(),
                    $this->makeMetersTab(),
                    $this->makeInvoicesTab(),
                    $this->makePaymentsTab(),
                    $this->makeStatisticsTab(),
                ])
        ]);
    }

    protected function makeContactTab(): Tabs\Tab {
        return Tabs\Tab::make(__('subscribers.sections.contact'))
            ->icon('heroicon-o-phone')
            ->schema([
                $this->makeInfoSection(__('subscribers.sections.basic'), [
                    TextEntry::make('name')
                        ->label(__('subscribers.labels.name')),
                    TextEntry::make('email')
                        ->label(__('subscribers.labels.email'))
                        ->copyable(),
                    TextEntry::make('phone')
                        ->label(__('subscribers.labels.phone'))
                        ->copyable(),
                    TextEntry::make('address')
                        ->label(__('subscribers.labels.address')),
                    TextEntry::make('status')
                        ->label(__('subscribers.labels.status'))
                        ->badge()
                        ->color(fn($state) => Subscriber::getStatusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Subscriber::getStatuses()[$state] ?? $state),
                ]),
                $this->makeInfoSection(__('subscribers.sections.status'), [
                    TextEntry::make('registration_date')
                        ->label(__('subscribers.labels.registration_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('notes')
                        ->label(__('subscribers.labels.notes'))
                        ->columnSpanFull(),
                ]),
            ]);
    }

    protected function makeMetersTab(): Tabs\Tab {
        return $this->makeRelationTab(
            __('subscribers.tabs.meters'),
            'heroicon-o-cpu-chip',
            'meters',
            [
                TextEntry::make('number')
                    ->label(__('meters.labels.number'))
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'number'])
                    ->url(fn($record) => route('filament.tenant.resources.tenant.meters.view', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                    ->openUrlInNewTab(),
                TextEntry::make('type')
                    ->label(__('meters.labels.type'))
                    ->badge()
                    ->color(fn($state) => Meter::getTypeColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Meter::getTypes()[$state] ?? $state)
                    ->extraAttributes(['data-field' => 'type']),
                TextEntry::make('model')
                    ->label(__('meters.labels.model'))
                    ->extraAttributes(['data-field' => 'model']),
                TextEntry::make('last_reading')
                    ->label(__('meters.labels.last_reading'))
                    ->numeric()
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'last_reading']),
                TextEntry::make('last_reading_date')
                    ->label(__('meters.labels.last_reading_date'))
                    ->date('d.m.Y')
                    ->extraAttributes(['data-field' => 'last_reading_date']),
                TextEntry::make('status')
                    ->label(__('meters.labels.status'))
                    ->badge()
                    ->color(fn($state) => Meter::getStatusColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Meter::getStatuses()[$state] ?? $state)
                    ->extraAttributes(['data-field' => 'status']),
            ]
        );
    }

    protected function makeInvoicesTab(): Tabs\Tab {
        return $this->makeRelationTab(
            __('subscribers.tabs.invoices'),
            'heroicon-o-receipt-percent',
            'invoices',
            [
                TextEntry::make('invoice_number')
                    ->label(__('invoices.labels.invoice_number'))
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'invoice_number'])
                    ->url(fn($record) => route('filament.tenant.resources.tenant.invoices.view', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                    ->openUrlInNewTab(),
                TextEntry::make('invoice_date')
                    ->label(__('invoices.labels.invoice_date'))
                    ->date('d.m.Y')
                    ->extraAttributes(['data-field' => 'invoice_date']),
                TextEntry::make('due_date')
                    ->label(__('invoices.labels.due_date'))
                    ->date('d.m.Y')
                    ->extraAttributes(['data-field' => 'due_date']),
                TextEntry::make('amount')
                    ->label(__('invoices.labels.amount'))
                    ->money('RUB')
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'amount']),
                TextEntry::make('total_amount')
                    ->label(__('invoices.labels.total_amount'))
                    ->money('RUB')
                    ->weight('bold')
                    ->color('primary')
                    ->extraAttributes(['data-field' => 'total_amount']),
                TextEntry::make('status')
                    ->label(__('invoices.labels.status'))
                    ->badge()
                    ->color(fn($state) => Invoice::getStatusColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Invoice::getStatuses()[$state] ?? $state)
                    ->extraAttributes(['data-field' => 'status']),
            ]
        );
    }

    protected function makePaymentsTab(): Tabs\Tab {
        return $this->makeRelationTab(
            __('subscribers.tabs.payments'),
            'heroicon-o-credit-card',
            'payments',
            [
                TextEntry::make('payment_number')
                    ->label(__('payments.labels.payment_number'))
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'payment_number'])
                    ->url(fn($record) => route('filament.tenant.resources.tenant.payments.view', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                    ->openUrlInNewTab(),
                TextEntry::make('payment_date')
                    ->label(__('payments.labels.payment_date'))
                    ->date('d.m.Y')
                    ->extraAttributes(['data-field' => 'payment_date']),
                TextEntry::make('payment_method')
                    ->label(__('payments.labels.payment_method'))
                    ->badge()
                    ->color(fn($state) => Payment::getTypeColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Payment::getTypes()[$state] ?? $state)
                    ->extraAttributes(['data-field' => 'payment_method']),
                TextEntry::make('amount')
                    ->label(__('payments.labels.amount'))
                    ->money('RUB')
                    ->weight('bold')
                    ->extraAttributes(['data-field' => 'amount']),
                TextEntry::make('reference_number')
                    ->label(__('payments.labels.reference'))
                    ->extraAttributes(['data-field' => 'reference_number']),
                TextEntry::make('status')
                    ->label(__('payments.labels.status'))
                    ->badge()
                    ->color(fn($state) => Payment::getStatusColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn($state) => Payment::getStatuses()[$state] ?? $state)
                    ->extraAttributes(['data-field' => 'status']),
            ]
        );
    }

    protected function makeStatisticsTab(): Tabs\Tab {
        return Tabs\Tab::make(__('subscribers.tabs.statistics'))
            ->icon('heroicon-o-chart-bar')
            ->schema([
                $this->makeInfoSection(__('subscribers.sections.statistics'), [
                    TextEntry::make('meters_count')
                        ->label(__('subscribers.labels.meters_count'))
                        ->numeric()
                        ->weight('bold')
                        ->icon('heroicon-o-cpu-chip')
                        ->color('primary')
                        ->default(fn() => $this->record->meters()->count()),
                    TextEntry::make('invoices_count')
                        ->label(__('subscribers.labels.invoices_count'))
                        ->numeric()
                        ->weight('bold')
                        ->icon('heroicon-o-receipt-percent')
                        ->color('info')
                        ->default(fn() => $this->record->invoices()->count()),
                    TextEntry::make('payments_count')
                        ->label(__('subscribers.labels.payments_count'))
                        ->numeric()
                        ->weight('bold')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->default(fn() => $this->record->payments()->count()),
                ]),
                $this->makeInfoSection(__('subscribers.sections.financial'), [
                    TextEntry::make('total_invoiced')
                        ->label(__('subscribers.labels.total_invoiced'))
                        ->money('RUB')
                        ->weight('bold')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->default(fn() => $this->record->invoices()->sum('total_amount')),
                    TextEntry::make('total_paid')
                        ->label(__('subscribers.labels.total_paid'))
                        ->money('RUB')
                        ->weight('bold')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->default(fn() => $this->record->payments()->where('status', 'completed')->sum('amount')),
                ]),
            ]);
    }
}