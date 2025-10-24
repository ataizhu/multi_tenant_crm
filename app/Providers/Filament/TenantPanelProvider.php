<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\TenantDatabaseMiddleware;
use App\Http\Middleware\TenantUrlMiddleware;

class TenantPanelProvider extends PanelProvider {
    public function panel(Panel $panel): Panel {
        return $panel
            ->id('tenant')
            ->path('tenant-crm')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->navigation(false) // Отключаем боковую навигацию
            ->renderHook('panels::topbar.start', fn(): string => view('components.top-navigation')->render())
            ->renderHook('panels::head.end', fn(): string => '
                <link rel="stylesheet" href="/css/tenant-panel.css">
                <script src="/js/tenant-context.js"></script>
            ')
            ->resources([
                \App\Filament\Resources\Tenant\SubscriberResource::class,
                \App\Filament\Resources\Tenant\MeterResource::class,
                \App\Filament\Resources\Tenant\MeterReadingResource::class,
                \App\Filament\Resources\Tenant\InvoiceResource::class,
                \App\Filament\Resources\Tenant\PaymentResource::class,
                \App\Filament\Resources\Tenant\ServiceResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\\Filament\\Tenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\\Filament\\Tenant\\Widgets')
            ->widgets([
                \App\Filament\Tenant\Widgets\SubscriberStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                TenantUrlMiddleware::class,
                TenantDatabaseMiddleware::class,
            ])
            ->authMiddleware([]);
    }
}
