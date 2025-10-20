# Multi-Tenant CRM System

Многопользовательская CRM система для управления жилищно-коммунальными услугами (ЖКХ) с архитектурой "база данных на тенанта".

## 🏗️ Архитектура

Система построена на принципе **"Database per Tenant"** - каждый клиент (тенант) имеет свою собственную базу данных PostgreSQL, что обеспечивает максимальную изоляцию данных и безопасность.

### Основные компоненты:

- **Центральная база данных** (`central_crm`) - управление тенантами и пользователями
- **Базы данных тенантов** (`tenant_*`) - индивидуальные данные каждого клиента
- **Laravel + Filament** - backend и админ-панель
- **PostgreSQL** - основная СУБД с поддержкой JSONB

## 🚀 Возможности

### Управление тенантами:
- ✅ Создание новых тенантов с автоматическим созданием БД
- ✅ Мягкое удаление в корзину с возможностью восстановления
- ✅ Полное удаление с очисткой всех данных
- ✅ Управление настройками через JSONB

### Система корзины:
- ✅ История удалений с указанием причины
- ✅ Информация о том, кто удалил и когда
- ✅ Восстановление тенантов из корзины
- ✅ Окончательное удаление из корзины

### Безопасность:
- ✅ Полная изоляция данных между тенантами
- ✅ Динамическое переключение БД через middleware
- ✅ Контроль доступа через Filament

## 📋 Требования

- PHP 8.1+
- PostgreSQL 13+
- Composer
- Node.js & NPM (для фронтенда)

## 🔧 Установка

### 1. Клонирование репозитория
```bash
git clone git@github.com:ataizhu/multi_tenant_crm.git
cd multi_tenant_crm
```

### 2. Установка зависимостей
```bash
composer install
npm install
```

### 3. Настройка окружения
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Настройка базы данных

Создайте базу данных PostgreSQL:
```sql
CREATE DATABASE central_crm;
CREATE USER postgres WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE central_crm TO postgres;
```

Обновите `.env` файл:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=central_crm
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 5. Запуск миграций
```bash
php artisan migrate
```

### 6. Создание администратора
```bash
php artisan make:filament-user
```

### 7. Запуск сервера
```bash
php artisan serve
```

Откройте http://127.0.0.1:8000/admin для доступа к админ-панели.

## 📁 Структура проекта

```
app/
├── Filament/Resources/           # Filament ресурсы
│   ├── TenantResource.php       # Управление тенантами
│   └── TenantTrashResource.php  # Корзина тенантов
├── Http/Middleware/             # Middleware
│   └── InitializeTenancy.php   # Переключение БД по домену
├── Models/                      # Eloquent модели
│   ├── Tenant.php              # Модель тенанта
│   ├── TenantTrash.php         # Модель корзины
│   └── User.php                # Модель пользователя
└── Services/                    # Сервисы
    └── TenantService.php       # Логика управления тенантами

database/
├── migrations/                  # Миграции центральной БД
│   └── 2025_10_20_161848_create_tenants_table.php
└── migrations/tenant/           # Миграции для БД тенантов
    └── 2025_10_20_164207_create_subscribers_table.php
```

## 🗄️ Структура базы данных

### Центральная БД (`central_crm`)

**Таблица `tenants`:**
- `id` - ID тенанта
- `name` - Название организации
- `domain` - Домен тенанта (уникальный)
- `database` - Имя БД тенанта (уникальное)
- `settings` - Настройки в JSONB формате
- `status` - Статус (active/inactive/suspended)
- `deleted` - Флаг мягкого удаления
- `timestamps` - Временные метки

**Таблица `tenant_trash`:**
- `id` - ID записи корзины
- `tenant_id` - Ссылка на тенанта
- `deleted_by` - Кто удалил
- `deleted_at` - Когда удален
- `deletion_reason` - Причина удаления
- `timestamps` - Временные метки

### БД тенантов (`tenant_*`)

**Таблица `subscribers`:**
- `id` - ID абонента
- `name` - Имя абонента
- `address` - Адрес
- `phone` - Телефон
- `email` - Email
- `status` - Статус абонента
- `balance` - Баланс
- `timestamps` - Временные метки

## 🔄 Workflow системы

### Создание тенанта:
1. Создается запись в таблице `tenants`
2. Автоматически создается БД `tenant_*`
3. Применяются миграции для БД тенанта
4. Тенант готов к использованию

### Удаление тенанта:
1. **Мягкое удаление**: запись помечается как `deleted=true`
2. Создается запись в `tenant_trash` с причиной удаления
3. БД тенанта остается нетронутой
4. **Восстановление**: флаг `deleted` сбрасывается, запись из корзины удаляется
5. **Полное удаление**: БД тенанта удаляется, записи из всех таблиц удаляются

## 🛠️ Основные команды

```bash
# Создание нового тенанта через tinker
php artisan tinker
$tenant = new App\Models\Tenant();
$tenant->name = 'Новая ЖКХ';
$tenant->domain = 'new-zhkh.zhkh.local';
$tenant->database = 'tenant_new_zhkh';
$tenant->status = 'active';
$tenant->save();

# Создание БД и миграций для тенанта
$tenantService = app(App\Services\TenantService::class);
$tenantService->createTenantDatabase($tenant);
$tenantService->runTenantMigrations($tenant);
```

## 🔐 Безопасность

- Каждый тенант имеет изолированную БД
- Middleware автоматически переключает подключение к БД по домену
- Все чувствительные данные исключены из Git (.env, пароли)
- Используется PostgreSQL с поддержкой JSONB для гибких настроек

## 📝 Лицензия

Этот проект создан для демонстрации архитектуры многопользовательских систем.

## 🤝 Вклад в проект

1. Fork проекта
2. Создайте feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit изменения (`git commit -m 'Add some AmazingFeature'`)
4. Push в branch (`git push origin feature/AmazingFeature`)
5. Откройте Pull Request

## 📞 Контакты

Проект создан для изучения архитектуры многопользовательских систем с Laravel и PostgreSQL.