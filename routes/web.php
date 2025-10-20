<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Маршрут для тенантской CRM
Route::get('/tenant/{tenant_id}/crm/{path?}', function ($tenant_id, $path = null) {
    $tenant = \App\Models\Tenant::find($tenant_id);
    if (!$tenant || $tenant->deleted) {
        abort(404, 'Тенант не найден');
    }

    // Сохраняем tenant_id в сессии
    session(['tenant_id' => $tenant_id]);

    // Настраиваем подключение к БД тенанта
    config([
        'database.connections.tenant' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => $tenant->database,
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]
    ]);

    // Перенаправляем на Filament панель тенанта
    $redirectUrl = $path ? "/tenant/{$path}" : '/tenant';
    return redirect($redirectUrl);
})->where('path', '.*')->name('tenant.crm');
