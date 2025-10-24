<?php

namespace App\Filament\Resources\Tenant\PaymentResource\Pages;

use App\Filament\Resources\Pages\BaseViewRecord;
use App\Filament\Resources\Tenant\PaymentResource;
use App\Filament\Traits\HasRelationTabs;
use App\Models\Payment;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewPayment extends BaseViewRecord {
    use HasRelationTabs;

    protected static string $resource = PaymentResource::class;

    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Tabs::make('Details')
                ->tabs([
                    $this->makeMainInfoTab(),
                    $this->makeInvoiceTab(),
                    $this->makeDetailsTab(),
                ])
        ]);
    }

    protected function makeMainInfoTab(): Tabs\Tab {
        return Tabs\Tab::make(__('common.main_info'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                $this->makeInfoSection(__('payments.sections.basic'), [
                    TextEntry::make('payment_number')
                        ->label(__('payments.labels.payment_number')),
                    TextEntry::make('amount')
                        ->label(__('payments.labels.amount'))
                        ->money('RUB'),
                    TextEntry::make('payment_date')
                        ->label(__('payments.labels.payment_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('payment_method')
                        ->label(__('payments.labels.payment_method'))
                        ->badge()
                        ->color(fn($state) => Payment::getTypeColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Payment::getTypes()[$state] ?? $state),
                    TextEntry::make('status')
                        ->label(__('payments.labels.status'))
                        ->badge()
                        ->color(fn($state) => Payment::getStatusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Payment::getStatuses()[$state] ?? $state),
                ]),
            ]);
    }

    protected function makeInvoiceTab(): Tabs\Tab {
        return Tabs\Tab::make(__('payments.tabs.invoice'))
            ->icon('heroicon-o-document-text')
            ->schema([
                TextEntry::make('invoice.invoice_number')
                    ->label(__('invoices.labels.invoice_number')),
                TextEntry::make('invoice.invoice_date')
                    ->label(__('invoices.labels.invoice_date'))
                    ->date('d.m.Y'),
                TextEntry::make('invoice.total_amount')
                    ->label(__('invoices.labels.total_amount'))
                    ->money('RUB'),
                TextEntry::make('invoice.status')
                    ->label(__('invoices.labels.status'))
                    ->badge(),
            ]);
    }

    protected function makeDetailsTab(): Tabs\Tab {
        return Tabs\Tab::make(__('payments.tabs.details'))
            ->icon('heroicon-o-document-text')
            ->schema([
                $this->makeInfoSection(__('payments.sections.transaction'), [
                    TextEntry::make('reference_number')
                        ->label(__('payments.labels.reference')),
                    TextEntry::make('transaction_id')
                        ->label(__('payments.labels.transaction_id')),
                ]),
                $this->makeInfoSection(__('payments.sections.status'), [
                    TextEntry::make('notes')
                        ->label(__('payments.labels.notes'))
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
