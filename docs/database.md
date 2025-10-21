# Документация по базе данных CRM для ЖКХ

## 1. Структура базы данных

### 1.1 Центральная база данных (central_crm)

#### Таблица tenants

```sql
CREATE TABLE tenants (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    database VARCHAR(255) NOT NULL UNIQUE,
    settings JSONB,
    status VARCHAR(50) DEFAULT 'active',
    deleted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Таблица tenant_trash

```sql
CREATE TABLE tenant_trash (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL,
    deleted_by BIGINT,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deletion_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### Таблица users

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Таблица cache

```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
```

#### Таблица jobs

```sql
CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
```

### 1.2 База данных клиента (tenant_*)

#### Таблица subscribers

```sql
CREATE TABLE subscribers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    apartment_number VARCHAR(10),
    building_number VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    balance DECIMAL(10,2) DEFAULT 0.00,
    registration_date DATE NOT NULL,
    additional_info JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 2. Основные запросы

### 2.1 Работа с тенантами

```sql
-- Получение активных тенантов
SELECT * FROM tenants WHERE deleted = FALSE;

-- Получение тенанта по домену
SELECT * FROM tenants WHERE domain = ? AND deleted = FALSE;

-- Получение тенантов из корзины
SELECT t.*, tt.deleted_by, tt.deleted_at, tt.deletion_reason
FROM tenants t
JOIN tenant_trash tt ON t.id = tt.tenant_id
WHERE t.deleted = TRUE;
```

### 2.2 Работа с абонентами

```sql
-- Получение списка активных абонентов
SELECT * FROM subscribers WHERE status = 'active';

-- Получение статистики по абонентам
SELECT 
    COUNT(*) as total_subscribers,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_subscribers,
    COUNT(CASE WHEN balance > 0 THEN 1 END) as subscribers_with_balance,
    SUM(balance) as total_balance
FROM subscribers;
```

### 2.3 Работа с корзиной тенантов

```sql
-- Мягкое удаление тенанта
UPDATE tenants SET deleted = TRUE WHERE id = ?;
INSERT INTO tenant_trash (tenant_id, deleted_by, deletion_reason) 
VALUES (?, ?, ?);

-- Восстановление тенанта из корзины
UPDATE tenants SET deleted = FALSE WHERE id = ?;
DELETE FROM tenant_trash WHERE tenant_id = ?;
```

## 3. Индексы

### 3.1 Центральная база данных

```sql
CREATE INDEX idx_tenants_domain ON tenants(domain);
CREATE INDEX idx_tenants_database ON tenants(database);
CREATE INDEX idx_tenants_deleted ON tenants(deleted);
CREATE INDEX idx_tenant_trash_tenant_id ON tenant_trash(tenant_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_cache_key ON cache(key);
CREATE INDEX idx_jobs_queue ON jobs(queue);
```

### 3.2 База данных клиента

```sql
CREATE INDEX idx_subscribers_status ON subscribers(status);
CREATE INDEX idx_subscribers_phone ON subscribers(phone);
CREATE INDEX idx_subscribers_email ON subscribers(email);
CREATE INDEX idx_subscribers_balance ON subscribers(balance);
```

## 4. Расширения PostgreSQL

```sql
-- Включение расширений для JSONB и UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
```

## 5. Триггеры

### 5.1 Автоматическое обновление updated_at

```sql
-- Функция для обновления updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Триггер для таблицы tenants
CREATE TRIGGER update_tenants_updated_at 
    BEFORE UPDATE ON tenants 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

-- Триггер для таблицы subscribers
CREATE TRIGGER update_subscribers_updated_at 
    BEFORE UPDATE ON subscribers 
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();
```

## 6. Миграции

### 6.1 Центральная база данных

Миграции находятся в `database/migrations/`:
- `0001_01_01_000000_create_users_table.php`
- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`
- `2025_10_20_161848_create_tenants_table.php`

### 6.2 База данных тенанта

Миграции находятся в `database/migrations/tenant/`:
- `2025_10_20_164207_create_subscribers_table.php`

## 7. Управление базами данных

### 7.1 Создание базы данных тенанта

```php
// Создание новой базы данных
$pdo = new PDO("pgsql:host=127.0.0.1;port=5432", $username, $password);
$pdo->exec("CREATE DATABASE \"{$database}\"");

// Применение миграций
php artisan migrate --database=tenant_migration --path=database/migrations/tenant
```

### 7.2 Удаление базы данных тенанта

```sql
-- Закрытие активных соединений
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'tenant_name' AND pid <> pg_backend_pid();

-- Удаление базы данных
DROP DATABASE IF EXISTS "tenant_name";
```