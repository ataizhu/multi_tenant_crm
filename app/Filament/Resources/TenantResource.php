<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\Tenant;
use App\Services\TenantService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource {
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Клиенты';
    protected static ?string $modelLabel = 'Клиент';
    protected static ?string $pluralModelLabel = 'Клиенты';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('domain')
                    ->label('Домен')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->suffix('.zhkh.local'),
                Forms\Components\TextInput::make('database')
                    ->label('База данных')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->prefix('tenant_'),
                Forms\Components\Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активный',
                        'inactive' => 'Неактивный',
                        'suspended' => 'Приостановлен',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\KeyValue::make('settings')
                    ->label('Настройки')
                    ->keyLabel('Ключ')
                    ->valueLabel('Значение'),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->query(Tenant::active()) // Показываем только активные тенанты
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('domain')
                    ->label('Домен')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('database')
                    ->label('База данных')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('crm')
                    ->label('CRM')
                    ->color('primary')
                    ->icon('heroicon-o-building-office')
                    ->url(fn(Tenant $record): string => "/tenant-crm/tenant/subscribers?tenant={$record->id}")
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('В корзину')
                    ->color('warning')
                    ->icon('heroicon-o-trash')
                    ->modalHeading('Переместить в корзину')
                    ->modalDescription('Тенант будет перемещен в корзину и может быть восстановлен позже.')
                    ->form([
                        Forms\Components\Textarea::make('deletion_reason')
                            ->label('Причина удаления')
                            ->placeholder('Укажите причину удаления (необязательно)')
                            ->rows(3),
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        $tenantService = app(TenantService::class);
                        $tenantService->softDeleteTenant($record, $data['deletion_reason'] ?? null);

                        Notification::make()
                            ->title('Тенант перемещен в корзину')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
