<?php

namespace App\Filament\Resources\Tenant;

use App\Filament\Resources\Tenant\SubscriberResource\Pages;
use App\Filament\Resources\Tenant\SubscriberResource\RelationManagers;
use App\Models\Subscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriberResource extends Resource {
    protected static ?string $model = Subscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Абоненты';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = null;

    protected static ?string $modelLabel = 'Абонент';

    protected static ?string $pluralModelLabel = 'Абоненты';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('ФИО')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('address')
                    ->label('Адрес')
                    ->required()
                    ->rows(3),

                Forms\Components\TextInput::make('apartment_number')
                    ->label('Номер квартиры')
                    ->maxLength(255),

                Forms\Components\TextInput::make('building_number')
                    ->label('Номер дома')
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активный',
                        'inactive' => 'Неактивный',
                        'blocked' => 'Заблокирован',
                    ])
                    ->default('active')
                    ->required(),

                Forms\Components\TextInput::make('balance')
                    ->label('Баланс')
                    ->numeric()
                    ->default(0)
                    ->step(0.01)
                    ->prefix('₽'),

                Forms\Components\DatePicker::make('registration_date')
                    ->label('Дата регистрации')
                    ->default(now())
                    ->required(),

                Forms\Components\KeyValue::make('additional_info')
                    ->label('Дополнительная информация')
                    ->keyLabel('Поле')
                    ->valueLabel('Значение'),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('ФИО')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('address')
                    ->label('Адрес')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('apartment_number')
                    ->label('Кв.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'blocked',
                    ]),

                Tables\Columns\TextColumn::make('balance')
                    ->label('Баланс')
                    ->money('RUB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('registration_date')
                    ->label('Дата регистрации')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активный',
                        'inactive' => 'Неактивный',
                        'blocked' => 'Заблокирован',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->recordUrl(fn($record): string => static::getUrl('view', ['record' => $record]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'view' => Pages\ViewSubscriber::route('/{record}'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
