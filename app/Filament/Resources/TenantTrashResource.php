<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantTrashResource\Pages;
use App\Filament\Resources\TenantTrashResource\RelationManagers;
use App\Models\TenantTrash;
use App\Services\TenantService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantTrashResource extends Resource {
    protected static ?string $model = TenantTrash::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'Корзина';
    protected static ?string $modelLabel = 'Удаленный тенант';
    protected static ?string $pluralModelLabel = 'Корзина';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.id')
                    ->label('ID тенанта')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Название тенанта')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.domain')
                    ->label('Домен')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deletedBy.name')
                    ->label('Удалил')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Дата удаления')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deletion_reason')
                    ->label('Причина')
                    ->searchable()
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\Filter::make('deleted_at')
                    ->form([
                        Forms\Components\DatePicker::make('deleted_from')
                            ->label('Удален с'),
                        Forms\Components\DatePicker::make('deleted_until')
                            ->label('Удален до'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['deleted_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('deleted_at', '>=', $date),
                            )
                            ->when(
                                $data['deleted_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('deleted_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('restore')
                    ->label('Восстановить')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Восстановить тенанта')
                    ->modalDescription('Тенант будет восстановлен и станет доступен для использования.')
                    ->action(function (TenantTrash $record): void {
                        $tenantService = app(TenantService::class);
                        $tenantService->restoreTenant($record->tenant);

                        Notification::make()
                            ->title('Тенант восстановлен')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('force_delete')
                    ->label('Удалить навсегда')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Удалить навсегда')
                    ->modalDescription('Тенант и его база данных будут удалены безвозвратно. Это действие нельзя отменить.')
                    ->action(function (TenantTrash $record): void {
                        $tenantService = app(TenantService::class);
                        $tenantService->forceDeleteTenant($record->tenant);

                        Notification::make()
                            ->title('Тенант удален навсегда')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\Action::make('restore_selected')
                        ->label('Восстановить выбранные')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Восстановить тенанты')
                        ->modalDescription('Выбранные тенанты будут восстановлены.')
                        ->action(function ($records): void {
                            $tenantService = app(TenantService::class);
                            foreach ($records as $record) {
                                $tenantService->restoreTenant($record->tenant);
                            }

                            Notification::make()
                                ->title('Тенанты восстановлены')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('force_delete_selected')
                        ->label('Удалить выбранные навсегда')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Удалить навсегда')
                        ->modalDescription('Выбранные тенанты и их базы данных будут удалены безвозвратно.')
                        ->action(function ($records): void {
                            $tenantService = app(TenantService::class);
                            foreach ($records as $record) {
                                $tenantService->forceDeleteTenant($record->tenant);
                            }

                            Notification::make()
                                ->title('Тенанты удалены навсегда')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListTenantTrashes::route('/'),
        ];
    }
}
