<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\MeterResource\Pages;
use App\Models\Meter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeterResource extends Resource {
    protected static ?string $model = Meter::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Счетчики';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Счетчик';

    protected static ?string $pluralModelLabel = 'Счетчики';

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

                        Forms\Components\TextInput::make('number')
                            ->label('Номер счетчика')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('type')
                            ->label('Тип счетчика')
                            ->options([
                                'water' => 'Вода',
                                'electricity' => 'Электричество',
                                'gas' => 'Газ',
                                'heating' => 'Отопление',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Техническая информация')
                    ->schema([
                        Forms\Components\TextInput::make('model')
                            ->label('Модель'),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Производитель'),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активный',
                                'inactive' => 'Неактивный',
                                'broken' => 'Сломан',
                                'replaced' => 'Заменен',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DatePicker::make('installation_date')
                            ->label('Дата установки'),
                    ])->columns(2),

                Forms\Components\Section::make('Показания')
                    ->schema([
                        Forms\Components\TextInput::make('last_reading')
                            ->label('Последнее показание')
                            ->numeric()
                            ->step(0.01)
                            ->default(0),

                        Forms\Components\DatePicker::make('last_reading_date')
                            ->label('Дата последнего показания'),
                    ])->columns(2),

                Forms\Components\Section::make('Поверка')
                    ->schema([
                        Forms\Components\DatePicker::make('verification_date')
                            ->label('Дата поверки'),

                        Forms\Components\DatePicker::make('next_verification_date')
                            ->label('Дата следующей поверки'),
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
                Tables\Columns\TextColumn::make('number')
                    ->label('Номер счетчика')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriber.name')
                    ->label('Абонент')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'primary' => 'water',
                        'success' => 'electricity',
                        'warning' => 'gas',
                        'danger' => 'heating',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'water' => 'Вода',
                        'electricity' => 'Электричество',
                        'gas' => 'Газ',
                        'heating' => 'Отопление',
                    }),

                Tables\Columns\TextColumn::make('last_reading')
                    ->label('Последнее показание')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_reading_date')
                    ->label('Дата показания')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'broken',
                        'secondary' => 'replaced',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Активный',
                        'inactive' => 'Неактивный',
                        'broken' => 'Сломан',
                        'replaced' => 'Заменен',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип счетчика')
                    ->options([
                        'water' => 'Вода',
                        'electricity' => 'Электричество',
                        'gas' => 'Газ',
                        'heating' => 'Отопление',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активный',
                        'inactive' => 'Неактивный',
                        'broken' => 'Сломан',
                        'replaced' => 'Заменен',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListMeters::route('/'),
            'create' => Pages\CreateMeter::route('/create'),
            'view' => Pages\ViewMeter::route('/{record}'),
            'edit' => Pages\EditMeter::route('/{record}/edit'),
        ];
    }
}
