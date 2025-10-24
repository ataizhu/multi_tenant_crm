<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\Pages;

use App\Filament\Resources\Tenant\SubscriberResource;
use App\Models\Meter;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Components\Tab;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;

class ViewSubscriber extends ViewRecord {
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
                ->label('Редактировать')
                ->color('primary')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->color('danger')
                ->icon('heroicon-o-trash'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                // Вкладки с детальной информацией
                Infolists\Components\Tabs::make('Subscriber Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Контактная информация')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Infolists\Components\Section::make('Основные контакты')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('phone')
                                                    ->label('Телефон')
                                                    ->icon('heroicon-o-phone')
                                                    ->copyable()
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                                Infolists\Components\TextEntry::make('email')
                                                    ->label('Email')
                                                    ->icon('heroicon-o-envelope')
                                                    ->copyable()
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Адрес')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('address')
                                            ->label('Полный адрес')
                                            ->icon('heroicon-o-map-pin')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('apartment_number')
                                                    ->label('Квартира')
                                                    ->icon('heroicon-o-home')
                                                    ->badge(),

                                                Infolists\Components\TextEntry::make('building_number')
                                                    ->label('Дом')
                                                    ->icon('heroicon-o-building-office')
                                                    ->badge(),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Регистрация')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('registration_date')
                                            ->label('Дата регистрации')
                                            ->date('d.m.Y')
                                            ->icon('heroicon-o-calendar')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Счетчики')
                            ->icon('heroicon-o-cpu-chip')
                            ->badge(fn() => $this->record->meters()->count())
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('meters')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('number')
                                            ->label('Номер')
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'number'])
                                            ->url(fn($record) => route('filament.tenant.resources.tenant.meters.edit', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                                            ->openUrlInNewTab(),

                                        Infolists\Components\TextEntry::make('type')
                                            ->label('Тип')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'water' => 'primary',
                                                'electricity' => 'success',
                                                'gas' => 'warning',
                                                'heating' => 'danger',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'water' => 'Вода',
                                                'electricity' => 'Электричество',
                                                'gas' => 'Газ',
                                                'heating' => 'Отопление',
                                            })
                                            ->extraAttributes(['data-field' => 'type']),

                                        Infolists\Components\TextEntry::make('model')
                                            ->label('Модель')
                                            ->extraAttributes(['data-field' => 'model']),

                                        Infolists\Components\TextEntry::make('last_reading')
                                            ->label('Показание')
                                            ->numeric()
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'last_reading']),

                                        Infolists\Components\TextEntry::make('last_reading_date')
                                            ->label('Дата')
                                            ->date('d.m.Y')
                                            ->extraAttributes(['data-field' => 'last_reading_date']),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Статус')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'active' => 'success',
                                                'inactive' => 'warning',
                                                'broken' => 'danger',
                                                'replaced' => 'secondary',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'active' => 'Активный',
                                                'inactive' => 'Неактивный',
                                                'broken' => 'Сломан',
                                                'replaced' => 'Заменен',
                                            })
                                            ->extraAttributes(['data-field' => 'status']),
                                    ])
                                    ->columns(6)
                                    ->extraAttributes(['class' => 'horizontal-list-item', 'data-type' => 'meter']),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Счета')
                            ->icon('heroicon-o-receipt-percent')
                            ->badge(fn() => $this->record->invoices()->count())
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('invoices')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('invoice_number')
                                            ->label('Номер')
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'invoice_number'])
                                            ->url(fn($record) => route('filament.tenant.resources.tenant.invoices.edit', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                                            ->openUrlInNewTab(),

                                        Infolists\Components\TextEntry::make('invoice_date')
                                            ->label('Дата')
                                            ->date('d.m.Y')
                                            ->extraAttributes(['data-field' => 'invoice_date']),

                                        Infolists\Components\TextEntry::make('due_date')
                                            ->label('Срок')
                                            ->date('d.m.Y')
                                            ->extraAttributes(['data-field' => 'due_date']),

                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Сумма')
                                            ->money('RUB')
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'amount']),

                                        Infolists\Components\TextEntry::make('total_amount')
                                            ->label('Итого')
                                            ->money('RUB')
                                            ->weight('bold')
                                            ->color('primary')
                                            ->extraAttributes(['data-field' => 'total_amount']),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Статус')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'sent' => 'primary',
                                                'paid' => 'success',
                                                'overdue' => 'danger',
                                                'cancelled' => 'secondary',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'draft' => 'Черновик',
                                                'sent' => 'Отправлен',
                                                'paid' => 'Оплачен',
                                                'overdue' => 'Просрочен',
                                                'cancelled' => 'Отменен',
                                            })
                                            ->extraAttributes(['data-field' => 'status']),
                                    ])
                                    ->columns(6)
                                    ->extraAttributes(['class' => 'horizontal-list-item', 'data-type' => 'invoice']),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Платежи')
                            ->icon('heroicon-o-credit-card')
                            ->badge(fn() => $this->record->payments()->count())
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('payments')
                                    ->label('')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('payment_number')
                                            ->label('Номер')
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'payment_number'])
                                            ->url(fn($record) => route('filament.tenant.resources.tenant.payments.edit', ['record' => $record->id, 'tenant' => request()->get('tenant')]))
                                            ->openUrlInNewTab(),

                                        Infolists\Components\TextEntry::make('payment_date')
                                            ->label('Дата')
                                            ->date('d.m.Y')
                                            ->extraAttributes(['data-field' => 'payment_date']),

                                        Infolists\Components\TextEntry::make('payment_method')
                                            ->label('Способ')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'cash' => 'Наличные',
                                                'card' => 'Карта',
                                                'bank_transfer' => 'Перевод',
                                                'mobile_payment' => 'Мобильный',
                                            })
                                            ->extraAttributes(['data-field' => 'payment_method']),

                                        Infolists\Components\TextEntry::make('amount')
                                            ->label('Сумма')
                                            ->money('RUB')
                                            ->weight('bold')
                                            ->extraAttributes(['data-field' => 'amount']),

                                        Infolists\Components\TextEntry::make('reference_number')
                                            ->label('Чек')
                                            ->extraAttributes(['data-field' => 'reference_number']),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Статус')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'refunded' => 'secondary',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'pending' => 'Ожидает',
                                                'completed' => 'Завершен',
                                                'failed' => 'Неудачный',
                                                'refunded' => 'Возвращен',
                                            })
                                            ->extraAttributes(['data-field' => 'status']),
                                    ])
                                    ->columns(6)
                                    ->extraAttributes(['class' => 'horizontal-list-item', 'data-type' => 'payment']),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Статистика')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Infolists\Components\Section::make('Общая статистика')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('meters_count')
                                                    ->label('Количество счетчиков')
                                                    ->numeric()
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-cpu-chip')
                                                    ->color('primary')
                                                    ->default(fn() => $this->record->meters()->count()),

                                                Infolists\Components\TextEntry::make('invoices_count')
                                                    ->label('Всего счетов')
                                                    ->numeric()
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-receipt-percent')
                                                    ->color('info')
                                                    ->default(fn() => $this->record->invoices()->count()),

                                                Infolists\Components\TextEntry::make('payments_count')
                                                    ->label('Всего платежей')
                                                    ->numeric()
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-credit-card')
                                                    ->color('success')
                                                    ->default(fn() => $this->record->payments()->count()),
                                            ]),
                                    ]),

                                Infolists\Components\Section::make('Финансовая статистика')
                                    ->schema([
                                        Infolists\Components\Grid::make(2)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('total_invoiced')
                                                    ->label('Общая сумма счетов')
                                                    ->money('RUB')
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-document-text')
                                                    ->color('info')
                                                    ->default(fn() => $this->record->invoices()->sum('total_amount')),

                                                Infolists\Components\TextEntry::make('total_paid')
                                                    ->label('Общая сумма платежей')
                                                    ->money('RUB')
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-banknotes')
                                                    ->color('success')
                                                    ->default(fn() => $this->record->payments()->where('status', 'completed')->sum('amount')),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->activeTab(0)
            ]);
    }
}