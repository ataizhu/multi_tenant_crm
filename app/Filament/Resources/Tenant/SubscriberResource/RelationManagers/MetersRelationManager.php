<?php

namespace App\Filament\Resources\Tenant\SubscriberResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MetersRelationManager extends RelationManager {
    protected static string $relationship = 'meters';

    protected static ?string $title = 'Счетчики';

    protected static ?string $modelLabel = 'Счетчик';

    protected static ?string $pluralModelLabel = 'Счетчики';

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Layout')
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Номер счетчика')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Тип счетчика')
                            ->options([
                                'water' => 'Вода',
                                'electricity' => 'Электричество',
                                'gas' => 'Газ',
                                'heating' => 'Отопление',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('model')
                            ->label('Модель'),

                        Forms\Components\TextInput::make('manufacturer')
                            ->label('Производитель'),
                    ])->columns(2),

                Forms\Components\Section::make('Показания и статус')
                    ->schema([
                        Forms\Components\TextInput::make('last_reading')
                            ->label('Последнее показание')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\DatePicker::make('last_reading_date')
                            ->label('Дата последнего показания'),

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
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Номер')
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

                Tables\Columns\TextColumn::make('model')
                    ->label('Модель')
                    ->searchable(),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('Производитель')
                    ->searchable(),

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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
