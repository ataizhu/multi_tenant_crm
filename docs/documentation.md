# Детальная документация CRM платформы

## 1. Архитектура системы

### 1.1 Структура баз данных

#### Центральная база данных (central)

```sql
-- Таблица клиентов
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) UNIQUE NOT NULL,
    database VARCHAR(255) UNIQUE NOT NULL,
    settings JSON,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица пользователей центра
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL,
    tenant_id BIGINT UNSIGNED NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);

-- Таблица истории действий
CREATE TABLE history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    action ENUM('create', 'update', 'delete') NOT NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### База данных клиента (tenant_*)

```sql
-- Таблица пользователей клиента
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'operator') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица абонентов
CREATE TABLE subscribers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица услуг
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    settings JSON,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица счетчиков
CREATE TABLE meters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    number VARCHAR(50) NOT NULL,
    last_value DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id)
);

-- Таблица показаний счетчиков
CREATE TABLE meter_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meter_id BIGINT UNSIGNED NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    reading_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (meter_id) REFERENCES meters(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Таблица счетов
CREATE TABLE bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'cancelled') DEFAULT 'draft',
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Таблица платежей
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Таблица тарифов
CREATE TABLE tariffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    settings JSON,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица налоговых ставок
CREATE TABLE tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    type ENUM('percentage', 'fixed') NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    deleted_reason TEXT NULL
);

-- Таблица налоговых отчетов
CREATE TABLE tax_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    total_tax DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'submitted', 'approved') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED NOT NULL,
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

### 1.2 Структура проекта

```
app/
├── Console/
│   └── Commands/
│       ├── CreateTenant.php
│       ├── DeleteTenant.php
│       └── TenantMaintenance.php
├── Http/
│   ├── Controllers/
│   │   ├── Central/
│   │   │   ├── TenantController.php
│   │   │   └── UserController.php
│   │   └── Tenant/
│   │       ├── SubscriberController.php
│   │       ├── ServiceController.php
│   │       ├── BillController.php
│   │       ├── PaymentController.php
│   │       └── TaxController.php
│   ├── Middleware/
│   │   ├── InitializeTenancy.php
│   │   └── PreventAccessFromCentralDomains.php
│   └── Requests/
├── Models/
│   ├── Central/
│   │   ├── Tenant.php
│   │   └── User.php
│   └── Tenant/
│       ├── Subscriber.php
│       ├── Service.php
│       ├── Bill.php
│       ├── Payment.php
│       └── TaxRate.php
├── Services/
│   ├── TenantService.php
│   ├── BillingService.php
│   └── TaxService.php
└── Providers/
    └── TenancyServiceProvider.php
```

## 2. Роли и права доступа

### 2.1 Суперадмин (super_admin)

- Управление всеми клиентами
- Создание/удаление клиентов
- Управление всеми админами
- Доступ к системным настройкам
- Доступ к логам системы
- Доступ к статистике по всем клиентам

### 2.2 Админ центра (admin)

- Управление своим клиентом
- Управление пользователями клиента
- Настройки клиента
- Доступ к логам клиента
- Доступ к статистике клиента

### 2.3 Менеджер клиента (manager)

- Управление абонентами
- Управление услугами
- Управление счетами
- Просмотр отчетов
- Управление платежами
- Управление налогами

### 2.4 Оператор клиента (operator)

- Просмотр абонентов
- Ввод показаний счетчиков
- Создание счетов
- Прием платежей
- Просмотр отчетов
- Ввод налоговых данных

## 3. Основные процессы

### 3.1 Создание нового клиента

1. Создание записи в центральной БД
2. Создание новой БД
3. Применение миграций
4. Создание админа клиента

### 3.2 Аутентификация

1. Определение клиента по домену
2. Подключение к нужной БД
3. Проверка прав доступа

### 3.3 Биллинг

1. Создание счета
2. Обновление баланса абонента
3. Прием платежа
4. Обновление баланса

### 3.4 Налоги

1. Настройка налоговых ставок
2. Расчет налогов при создании счета
3. Формирование налоговых отчетов
4. Экспорт налоговых документов
5. Интеграция с налоговыми системами

## 4. API Endpoints

### 4.1 Центральная часть

```
POST /api/tenants
GET /api/tenants
PUT /api/tenants/{id}
DELETE /api/tenants/{id}
```

### 4.2 Клиентская часть

```
GET /api/subscribers
POST /api/subscribers
GET /api/bills
POST /api/bills
POST /api/payments
GET /api/tax-rates
POST /api/tax-rates
GET /api/tax-reports
POST /api/tax-reports
```

## 5. Безопасность

### 5.1 Изоляция данных

- Каждый клиент имеет свою БД
- Нет прямого доступа между клиентами
- Все запросы проверяются на принадлежность к клиенту

### 5.2 Аутентификация

- JWT токены
- Refresh токены
- Двухфакторная аутентификация
- Блокировка после неудачных попыток

### 5.3 Аудит

- Логирование всех действий
- История изменений
- IP адреса
- User Agent

## 6. Оптимизация

### 6.1 Кэширование

- Redis для кэширования
- Кэширование запросов
- Кэширование отчетов

### 6.2 Очереди

- Очереди для тяжелых операций
- Очереди для уведомлений
- Очереди для отчетов

### 6.3 Мониторинг

- Мониторинг производительности
- Мониторинг ошибок
- Мониторинг нагрузки

## 7. Развертывание

### 7.1 Требования

- PHP 8.1+
- MySQL 8.0+
- Redis
- Nginx/Apache
- SSL сертификат

### 7.2 Процесс развертывания

1. Клонирование репозитория
2. Установка зависимостей
3. Настройка окружения
4. Применение миграций
5. Настройка SSL
6. Настройка доменов

### 7.3 Бэкапы

- Автоматические бэкапы БД
- Бэкапы файлов
- Репликация БД
