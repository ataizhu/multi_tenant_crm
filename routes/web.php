<?php

use App\Http\Controllers\TenantAuthController;
use Illuminate\Support\Facades\Route;

// Маршруты для аутентификации тенанта
Route::middleware(['tenant.url'])->group(function () {
    Route::get('/login', [TenantAuthController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [TenantAuthController::class, 'login']);
    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('tenant.logout');
});

// Маршрут для перенаправления авторизованных пользователей
Route::middleware(['tenant.url', 'auth:tenant'])->group(function () {
    Route::get('/', function () {
        $tenant = request()->get('tenant');
        return redirect()->route('filament.tenant.pages.dashboard', ['tenant' => $tenant->domain]);
    });
});