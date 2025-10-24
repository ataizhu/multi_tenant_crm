<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantUserResource\Pages;
use App\Models\TenantUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantUserResource extends Resource {
    protected static ?string $model = TenantUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователь';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?string $navigationGroup = 'Управление';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(fn($record) => $record === null)
                            ->minLength(8)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => bcrypt($state)),
                    ])->columns(2),

                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Select::make('locale')
                            ->label('Язык')
                            ->options([
                                'ru' => 'Русский',
                                'en' => 'English',
                            ])
                            ->default('ru')
                            ->required(),

                        Forms\Components\Toggle::make('is_admin')
                            ->label('Администратор')
                            ->default(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('locale')
                    ->label('Язык')
                    ->colors([
                        'info' => 'ru',
                        'warning' => 'en',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ru' => 'Русский',
                        'en' => 'English',
                    }),

                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Админ')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-user'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_admin')
                    ->label('Роль')
                    ->options([
                        true => 'Администраторы',
                        false => 'Пользователи',
                    ]),

                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        true => 'Активные',
                        false => 'Неактивные',
                    ]),

                Tables\Filters\SelectFilter::make('locale')
                    ->label('Язык')
                    ->options([
                        'ru' => 'Русский',
                        'en' => 'English',
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
            ])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()
            ->where('tenant_id', request()->get('tenant')?->id);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array {
        $data['tenant_id'] = request()->get('tenant')?->id;
        return $data;
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListTenantUsers::route('/'),
            'create' => Pages\CreateTenantUser::route('/create'),
            'edit' => Pages\EditTenantUser::route('/{record}/edit'),
        ];
    }
}
