<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource {
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Платежи';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Платеж';

    protected static ?string $pluralModelLabel = 'Платежи';

    protected static ?string $navigationGroup = null;

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('subscriber_id')
                            ->label('Абонент')
                            ->relationship('subscriber', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('invoice_id')
                            ->label('Счет')
                            ->relationship('invoice', 'invoice_number')
                            ->searchable(),

                        Forms\Components\TextInput::make('payment_number')
                            ->label('Номер платежа')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('amount')
                            ->label('Сумма')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Детали платежа')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Дата платежа')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Банковская карта',
                                'bank_transfer' => 'Банковский перевод',
                                'mobile_payment' => 'Мобильный платеж',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => 'Ожидает',
                                'completed' => 'Завершен',
                                'failed' => 'Неудачный',
                                'refunded' => 'Возвращен',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('reference_number')
                            ->label('Номер чека/транзакции'),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Примечания')
                            ->rows(3),

                        Forms\Components\KeyValue::make('additional_info')
                            ->label('Дополнительная информация')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('Номер платежа')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriber.name')
                    ->label('Абонент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Счет')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('KGS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Дата платежа')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        'bank_transfer' => 'Банковский перевод',
                        'mobile_payment' => 'Мобильный платеж',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'completed' => 'Завершен',
                        'failed' => 'Неудачный',
                        'refunded' => 'Возвращен',
                    }),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Номер чека')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'completed' => 'Завершен',
                        'failed' => 'Неудачный',
                        'refunded' => 'Возвращен',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Банковская карта',
                        'bank_transfer' => 'Банковский перевод',
                        'mobile_payment' => 'Мобильный платеж',
                    ]),

                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('С'),
                        Forms\Components\DatePicker::make('until')
                            ->label('По'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($query, $date) => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
