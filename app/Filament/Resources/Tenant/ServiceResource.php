<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource {
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Услуги';

    protected static ?string $modelLabel = 'Услуга';

    protected static ?string $pluralModelLabel = 'Услуги';

    protected static ?string $navigationGroup = 'Справочники';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Код')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Forms\Components\Select::make('type')
                            ->label('Тип услуги')
                            ->options([
                                'utility' => 'Коммунальная',
                                'maintenance' => 'Техническое обслуживание',
                                'additional' => 'Дополнительная',
                                'penalty' => 'Штраф',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Ценообразование')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->step(0.01)
                            ->required(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Единица измерения')
                            ->default('month')
                            ->helperText('месяц, кв.м, кВт*ч, м³'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),

                        Forms\Components\Toggle::make('is_metered')
                            ->label('Зависит от счетчиков')
                            ->helperText('Зависит ли от показаний счетчиков'),
                    ])->columns(2),

                Forms\Components\Section::make('Правила расчета')
                    ->schema([
                        Forms\Components\KeyValue::make('calculation_rules')
                            ->label('Правила расчета')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значение'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Код')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->colors([
                        'primary' => 'utility',
                        'success' => 'maintenance',
                        'warning' => 'additional',
                        'danger' => 'penalty',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'utility' => 'Коммунальная',
                        'maintenance' => 'Техническое обслуживание',
                        'additional' => 'Дополнительная',
                        'penalty' => 'Штраф',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('KGS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Единица')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_metered')
                    ->label('По счетчикам')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип услуги')
                    ->options([
                        'utility' => 'Коммунальная',
                        'maintenance' => 'Техническое обслуживание',
                        'additional' => 'Дополнительная',
                        'penalty' => 'Штраф',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активна'),

                Tables\Filters\TernaryFilter::make('is_metered')
                    ->label('По счетчикам'),
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
            ->defaultSort('name');
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
