<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\MeterReadingResource\Pages;
use App\Models\MeterReading;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeterReadingResource extends Resource {
    protected static ?string $model = MeterReading::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Показания счетчиков';

    protected static ?string $modelLabel = 'Показание счетчика';

    protected static ?string $pluralModelLabel = 'Показания счетчиков';

    protected static ?string $navigationGroup = 'Управление';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\Select::make('meter_id')
                            ->label('Счетчик')
                            ->relationship('meter', 'number')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('reading')
                            ->label('Показание')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Forms\Components\DatePicker::make('reading_date')
                            ->label('Дата показания')
                            ->default(now())
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Forms\Components\TextInput::make('consumption')
                            ->label('Потребление')
                            ->numeric()
                            ->step(0.01)
                            ->helperText('Разность с предыдущим показанием'),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'new' => 'Новое',
                                'verified' => 'Проверено',
                                'rejected' => 'Отклонено',
                            ])
                            ->default('new')
                            ->required(),

                        Forms\Components\TextInput::make('verified_by')
                            ->label('Проверил'),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Дата проверки'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Примечания')
                            ->rows(3),
                    ])->columns(2),

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
                Tables\Columns\TextColumn::make('meter.number')
                    ->label('Номер счетчика')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('meter.subscriber.name')
                    ->label('Абонент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reading')
                    ->label('Показание')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('consumption')
                    ->label('Потребление')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reading_date')
                    ->label('Дата показания')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'primary' => 'new',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'new' => 'Новое',
                        'verified' => 'Проверено',
                        'rejected' => 'Отклонено',
                    }),

                Tables\Columns\TextColumn::make('verified_by')
                    ->label('Проверил')
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
                        'new' => 'Новое',
                        'verified' => 'Проверено',
                        'rejected' => 'Отклонено',
                    ]),

                Tables\Filters\Filter::make('reading_date')
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
                                fn($query, $date) => $query->whereDate('reading_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn($query, $date) => $query->whereDate('reading_date', '<=', $date),
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
            ->defaultSort('reading_date', 'desc');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListMeterReadings::route('/'),
            'create' => Pages\CreateMeterReading::route('/create'),
            'edit' => Pages\EditMeterReading::route('/{record}/edit'),
        ];
    }
}
