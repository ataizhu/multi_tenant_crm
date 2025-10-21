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
                // Заголовок с основной информацией
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                // Аватар
                                Infolists\Components\Group::make()
                                    ->schema([
                                        Infolists\Components\ViewEntry::make('avatar')
                                            ->view('components.subscriber-avatar')
                                            ->viewData(fn($record) => ['subscriber' => $record]),
                                    ])
                                    ->columnSpan(1),

                                // Основная информация
                                Infolists\Components\Group::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight('bold')
                                            ->color('primary'),

                                        Infolists\Components\TextEntry::make('status')
                                            ->label('Статус')
                                            ->badge()
                                            ->size('lg')
                                            ->color(fn(string $state): string => match ($state) {
                                                'active' => 'success',
                                                'inactive' => 'warning',
                                                'blocked' => 'danger',
                                            })
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'active' => 'Активный',
                                                'inactive' => 'Неактивный',
                                                'blocked' => 'Заблокирован',
                                            }),

                                        Infolists\Components\TextEntry::make('registration_date')
                                            ->label('Дата регистрации')
                                            ->date('d.m.Y')
                                            ->icon('heroicon-o-calendar')
                                            ->color('gray'),
                                    ])
                                    ->columnSpan(2),

                                // Баланс
                                Infolists\Components\Group::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('balance')
                                            ->label('Баланс')
                                            ->money('RUB')
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                            ->weight('bold')
                                            ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                                            ->icon(fn($state) => $state >= 0 ? 'heroicon-o-banknotes' : 'heroicon-o-exclamation-triangle'),

                                        Infolists\Components\TextEntry::make('apartment_number')
                                            ->label('Квартира')
                                            ->badge()
                                            ->icon('heroicon-o-home')
                                            ->color('info'),

                                        Infolists\Components\TextEntry::make('building_number')
                                            ->label('Дом')
                                            ->badge()
                                            ->icon('heroicon-o-building-office')
                                            ->color('info'),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(false),

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
                                Infolists\Components\Section::make('Установленные счетчики')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('meters')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        // Основная информация о счетчике
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('number')
                                                                    ->label('Номер счетчика')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-hashtag'),

                                                                Infolists\Components\TextEntry::make('type')
                                                                    ->label('Тип')
                                                                    ->badge()
                                                                    ->size('lg')
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
                                                                    }),

                                                                Infolists\Components\TextEntry::make('model')
                                                                    ->label('Модель')
                                                                    ->icon('heroicon-o-cog-6-tooth'),
                                                            ]),

                                                        // Показания и статус
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('last_reading')
                                                                    ->label('Последнее показание')
                                                                    ->numeric()
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-calculator'),

                                                                Infolists\Components\TextEntry::make('last_reading_date')
                                                                    ->label('Дата показания')
                                                                    ->date('d.m.Y')
                                                                    ->icon('heroicon-o-calendar'),

                                                                Infolists\Components\TextEntry::make('status')
                                                                    ->label('Статус')
                                                                    ->badge()
                                                                    ->size('lg')
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
                                                                    }),
                                                            ]),
                                                    ]),
                                            ])
                                            ->columns(1),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Счета')
                            ->icon('heroicon-o-receipt-percent')
                            ->badge(fn() => $this->record->invoices()->count())
                            ->schema([
                                Infolists\Components\Section::make('История счетов')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('invoices')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        // Основная информация о счете
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('invoice_number')
                                                                    ->label('Номер счета')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-document-text'),

                                                                Infolists\Components\TextEntry::make('invoice_date')
                                                                    ->label('Дата счета')
                                                                    ->date('d.m.Y')
                                                                    ->icon('heroicon-o-calendar'),

                                                                Infolists\Components\TextEntry::make('due_date')
                                                                    ->label('Срок оплаты')
                                                                    ->date('d.m.Y')
                                                                    ->icon('heroicon-o-clock'),
                                                            ]),

                                                        // Сумма и статус
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('amount')
                                                                    ->label('Сумма')
                                                                    ->money('RUB')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-banknotes'),

                                                                Infolists\Components\TextEntry::make('total_amount')
                                                                    ->label('Итого')
                                                                    ->money('RUB')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->color('primary'),

                                                                Infolists\Components\TextEntry::make('status')
                                                                    ->label('Статус')
                                                                    ->badge()
                                                                    ->size('lg')
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
                                                                    }),
                                                            ]),
                                                    ]),
                                            ])
                                            ->columns(1),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('Платежи')
                            ->icon('heroicon-o-credit-card')
                            ->badge(fn() => $this->record->payments()->count())
                            ->schema([
                                Infolists\Components\Section::make('История платежей')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('payments')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\Grid::make(2)
                                                    ->schema([
                                                        // Основная информация о платеже
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('payment_number')
                                                                    ->label('Номер платежа')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-credit-card'),

                                                                Infolists\Components\TextEntry::make('payment_date')
                                                                    ->label('Дата платежа')
                                                                    ->date('d.m.Y')
                                                                    ->icon('heroicon-o-calendar'),

                                                                Infolists\Components\TextEntry::make('payment_method')
                                                                    ->label('Способ оплаты')
                                                                    ->badge()
                                                                    ->color('info')
                                                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                                                        'cash' => 'Наличные',
                                                                        'card' => 'Банковская карта',
                                                                        'bank_transfer' => 'Банковский перевод',
                                                                        'mobile_payment' => 'Мобильный платеж',
                                                                    }),
                                                            ]),

                                                        // Сумма и статус
                                                        Infolists\Components\Group::make()
                                                            ->schema([
                                                                Infolists\Components\TextEntry::make('amount')
                                                                    ->label('Сумма')
                                                                    ->money('RUB')
                                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                                    ->weight('bold')
                                                                    ->icon('heroicon-o-banknotes'),

                                                                Infolists\Components\TextEntry::make('reference_number')
                                                                    ->label('Номер чека')
                                                                    ->icon('heroicon-o-receipt-percent'),

                                                                Infolists\Components\TextEntry::make('status')
                                                                    ->label('Статус')
                                                                    ->badge()
                                                                    ->size('lg')
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
                                                                    }),
                                                            ]),
                                                    ]),
                                            ])
                                            ->columns(1),
                                    ]),
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
                    ->activeTab(1)
            ]);
    }
}