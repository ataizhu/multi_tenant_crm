<?php

namespace App\Filament\Resources\Tenant\InvoiceResource\Pages;

use App\Filament\Resources\Pages\BaseViewRecord;
use App\Filament\Resources\Tenant\InvoiceResource;
use App\Filament\Traits\HasRelationTabs;
use App\Models\Invoice;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ViewInvoice extends BaseViewRecord {
    use HasRelationTabs;

    protected static string $resource = InvoiceResource::class;

    public function infolist(Infolist $infolist): Infolist {
        return $infolist->schema([
            Tabs::make('Details')
                ->tabs([
                    $this->makeMainInfoTab(),
                    $this->makePaymentsTab(),
                    $this->makeDetailsTab(),
                ])
        ]);
    }

    protected function makeMainInfoTab(): Tabs\Tab {
        return Tabs\Tab::make(__('common.main_info'))
            ->icon('heroicon-o-information-circle')
            ->schema([
                $this->makeInfoSection(__('invoices.sections.basic'), [
                    TextEntry::make('invoice_number')
                        ->label(__('invoices.labels.invoice_number')),
                    TextEntry::make('invoice_date')
                        ->label(__('invoices.labels.invoice_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('due_date')
                        ->label(__('invoices.labels.due_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('status')
                        ->label(__('invoices.labels.status'))
                        ->badge()
                        ->color(fn($state) => Invoice::getStatusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn($state) => Invoice::getStatuses()[$state] ?? $state),
                ]),
                $this->makeInfoSection(__('invoices.sections.period'), [
                    TextEntry::make('period_start')
                        ->label(__('invoices.labels.period_start'))
                        ->date('d.m.Y'),
                    TextEntry::make('period_end')
                        ->label(__('invoices.labels.period_end'))
                        ->date('d.m.Y'),
                    TextEntry::make('amount')
                        ->label(__('invoices.labels.amount'))
                        ->money('RUB'),
                    TextEntry::make('tax_amount')
                        ->label(__('invoices.labels.tax_amount'))
                        ->money('RUB'),
                    TextEntry::make('total_amount')
                        ->label(__('invoices.labels.total_amount'))
                        ->money('RUB'),
                ]),
            ]);
    }

    protected function makePaymentsTab(): Tabs\Tab {
        return $this->makeRelationTab(
            __('invoices.tabs.payments'),
            'heroicon-o-credit-card',
            'payments',
            [
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
                    ->badge(),
                TextEntry::make('status')
                    ->label(__('payments.labels.status'))
                    ->badge(),
            ]
        );
    }

    protected function makeDetailsTab(): Tabs\Tab {
        return Tabs\Tab::make(__('invoices.tabs.details'))
            ->icon('heroicon-o-document-text')
            ->schema([
                $this->makeInfoSection(__('invoices.sections.status'), [
                    TextEntry::make('notes')
                        ->label(__('invoices.labels.notes'))
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
