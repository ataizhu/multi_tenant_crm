<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Subscriber;
use App\Models\Invoice;
use App\Models\Payment;

class SubscriberStatsWidget extends BaseWidget {
    protected function getStats(): array {
        return [
            Stat::make('Всего абонентов', Subscriber::count())
                ->description('Зарегистрировано в системе')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Активные абоненты', Subscriber::where('status', 'active')->count())
                ->description('Активно используют услуги')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Общий баланс', number_format(Subscriber::sum('balance'), 0, ',', ' ') . ' ₽')
                ->description('Сумма всех балансов')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Задолженность', number_format(Subscriber::where('balance', '<', 0)->sum('balance'), 0, ',', ' ') . ' ₽')
                ->description('Отрицательные балансы')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
