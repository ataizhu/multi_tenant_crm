<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource {
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Счета';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Счет';

    protected static ?string $pluralModelLabel = 'Счета';

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

                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Номер счета')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Дата счета')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Срок оплаты')
                            ->default(now()->addDays(30))
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Период и суммы')
                    ->schema([
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Период с')
                            ->required(),

                        Forms\Components\DatePicker::make('period_end')
                            ->label('Период по')
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Сумма')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Налог')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Итого')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Статус и примечания')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'draft' => 'Черновик',
                                'sent' => 'Отправлен',
                                'paid' => 'Оплачен',
                                'overdue' => 'Просрочен',
                                'cancelled' => 'Отменен',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Примечания')
                            ->rows(3),
                    ])->columns(1),

                Forms\Components\Section::make('Детализация')
                    ->schema([
                        Forms\Components\KeyValue::make('line_items')
                            ->label('Позиции счета')
                            ->keyLabel('Услуга')
                            ->valueLabel('Сумма'),
                    ]),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
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
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Номер счета')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriber.name')
                    ->label('Абонент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Дата счета')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Срок оплаты')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('KGS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Итого')
                    ->money('KGS')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => 'draft',
                        'primary' => 'sent',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'secondary' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'sent' => 'Отправлен',
                        'paid' => 'Оплачен',
                        'overdue' => 'Просрочен',
                        'cancelled' => 'Отменен',
                    }),

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
                        'draft' => 'Черновик',
                        'sent' => 'Отправлен',
                        'paid' => 'Оплачен',
                        'overdue' => 'Просрочен',
                        'cancelled' => 'Отменен',
                    ]),

                Tables\Filters\Filter::make('invoice_date')
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
                                fn($query, $date) => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('invoice_date', '<=', $date),
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
            ->defaultSort('invoice_date', 'desc');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
