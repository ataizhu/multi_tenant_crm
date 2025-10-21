# Документация Multi-Tenant CRM системы

## 1. Архитектура системы

### 1.1 Общая схема

Система построена на принципе **Database-per-Tenant** - каждый клиент (тенант) имеет свою собственную базу данных PostgreSQL, что обеспечивает максимальную изоляцию данных и безопасность.

### 1.2 Основные компоненты

#### Центральная часть
- **База данных**: `central_crm` - управление тенантами и пользователями
- **Админ панель**: Filament AdminPanelProvider - управление тенантами
- **Пользователи**: Администраторы системы

#### Тенантская часть
- **База данных**: `tenant_*` - индивидуальные данные каждого клиента
- **CRM панель**: Filament TenantPanelProvider - управление абонентами
- **Пользователи**: Пока нет аутентификации (планируется)

### 1.3 Middleware система

#### TenantDatabaseMiddleware
```php
// app/Http/Middleware/TenantDatabaseMiddleware.php
class TenantDatabaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $tenantId = $request->route('tenant_id') ?? session('tenant_id');
        
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            $this->configureTenantDatabase($tenant);
        }
        
        return $next($request);
    }
}
```

#### TenantServiceProvider
```php
// app/Providers/TenantServiceProvider.php
class TenantServiceProvider extends ServiceProvider
{
    public function boot()
    {
        app()->booted(function () {
            $tenantId = session('tenant_id');
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    $this->configureTenantDatabase($tenant);
                }
            }
        });
    }
}
```

### 1.4 Сервисы

#### TenantService
```php
// app/Services/TenantService.php
class TenantService
{
    public function createTenant(array $data): Tenant
    public function createTenantDatabase(Tenant $tenant): void
    public function runTenantMigrations(Tenant $tenant): void
    public function softDeleteTenant(Tenant $tenant, ?string $reason = null): void
    public function restoreTenant(Tenant $tenant): void
    public function forceDeleteTenant(Tenant $tenant): void
}
```

## 2. Структура базы данных

### 2.1 Центральная база данных (central_crm)

#### Таблица tenants
- `id` - ID тенанта
- `name` - Название организации
- `domain` - Домен тенанта (уникальный)
- `database` - Имя БД тенанта (уникальное)
- `settings` - Настройки в JSONB формате
- `status` - Статус (active/inactive/suspended)
- `deleted` - Флаг мягкого удаления
- `timestamps` - Временные метки

#### Таблица tenant_trash
- `id` - ID записи корзины
- `tenant_id` - Ссылка на тенанта
- `deleted_by` - Кто удалил
- `deleted_at` - Когда удален
- `deletion_reason` - Причина удаления
- `timestamps` - Временные метки

#### Таблица users
- `id` - ID пользователя
- `name` - Имя пользователя
- `email` - Email (уникальный)
- `password` - Хэшированный пароль
- `timestamps` - Временные метки

### 2.2 База данных тенанта (tenant_*)

#### Таблица subscribers
- `id` - ID абонента
- `name` - ФИО абонента
- `address` - Адрес
- `apartment_number` - Номер квартиры
- `building_number` - Номер дома
- `phone` - Телефон
- `email` - Email
- `status` - Статус абонента
- `balance` - Баланс
- `registration_date` - Дата регистрации
- `additional_info` - Дополнительная информация (JSONB)
- `timestamps` - Временные метки

## 3. Основные процессы

### 3.1 Создание нового тенанта

1. Создание записи в таблице `tenants`
2. Создание новой БД `tenant_*`
3. Применение миграций для БД тенанта
4. Тенант готов к использованию

### 3.2 Мягкое удаление тенанта

1. Установка флага `deleted = true` в таблице `tenants`
2. Создание записи в таблице `tenant_trash`
3. БД тенанта остается нетронутой

### 3.3 Восстановление тенанта

1. Сброс флага `deleted = false` в таблице `tenants`
2. Удаление записи из таблицы `tenant_trash`

### 3.4 Полное удаление тенанта

1. Удаление БД тенанта
2. Удаление записи из таблицы `tenant_trash`
3. Удаление записи из таблицы `tenants`

## 4. Маршруты

### 4.1 Центральная админка
- `/admin` - Главная страница админки
- `/admin/tenants` - Управление тенантами
- `/admin/tenant-trashes` - Корзина тенантов

### 4.2 CRM тенанта
- `/tenant/{tenant_id}/crm` - Главная страница CRM
- `/tenant/{tenant_id}/crm/tenant/subscribers` - Управление абонентами

## 5. Безопасность

### 5.1 Изоляция данных

- Каждый тенант имеет изолированную БД
- Middleware автоматически переключает подключение к БД
- Нет прямого доступа между тенантами

### 5.2 Базовая аутентификация

- Аутентификация только для центральной админки
- Аутентификация для тенантов пока не реализована (планируется)

## 6. Развертывание

### 6.1 Требования

- PHP 8.2+
- PostgreSQL 13+
- Composer
- Node.js & NPM

### 6.2 Процесс развертывания

1. Клонирование репозитория
2. Установка зависимостей
3. Настройка окружения
4. Применение миграций
5. Создание администратора
6. Запуск сервера

### 6.3 Настройка базы данных

```sql
CREATE DATABASE central_crm;
CREATE USER postgres WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE central_crm TO postgres;
```

## 7. Текущее состояние

### 7.1 Реализованные функции

- ✅ Создание и управление тенантами
- ✅ Мягкое удаление в корзину
- ✅ Восстановление из корзины
- ✅ Полное удаление с БД
- ✅ Управление абонентами
- ✅ Две отдельные Filament панели
- ✅ Middleware для переключения БД

### 7.2 Планируемые функции

- 🔄 Аутентификация для тенантов
- 🔄 Услуги и тарифы
- 🔄 Счета и платежи
- 🔄 Отчеты и аналитика
- 🔄 API для интеграций
- 🔄 Доменная архитектура

## 8. Структура проекта

```
app/
├── Filament/Resources/
│   ├── TenantResource.php              # Управление тенантами
│   ├── TenantTrashResource.php         # Корзина тенантов
│   └── Tenant/
│       └── SubscriberResource.php      # Управление абонентами
├── Http/Middleware/
│   └── TenantDatabaseMiddleware.php    # Переключение БД
├── Models/
│   ├── Tenant.php                      # Модель тенанта
│   ├── TenantTrash.php                 # Модель корзины
│   ├── User.php                        # Модель пользователя
│   └── Subscriber.php                  # Модель абонента
├── Providers/
│   └── TenantServiceProvider.php       # Сервис провайдер
└── Services/
    └── TenantService.php               # Логика управления тенантами

database/
├── migrations/                         # Миграции центральной БД
│   ├── create_tenants_table.php
│   └── create_users_table.php
└── migrations/tenant/                  # Миграции для БД тенантов
    └── create_subscribers_table.php
```