# Документация по базе данных CRM для ЖКХ

## 1. Структура базы данных

### 1.1 Центральная база данных

#### Таблица tenants

```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    database VARCHAR(255) NOT NULL UNIQUE,
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Таблица users

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager', 'operator') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

#### Таблица history

```sql
CREATE TABLE history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 1.2 База данных клиента

#### Таблица subscribers

```sql
CREATE TABLE subscribers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Таблица services

```sql
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    tariff_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id),
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)
);
```

#### Таблица meters

```sql
CREATE TABLE meters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(50) NOT NULL,
    type ENUM('water', 'electricity', 'gas') NOT NULL,
    status ENUM('active', 'inactive', 'broken') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id)
);
```

#### Таблица meter_readings

```sql
CREATE TABLE meter_readings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meter_id BIGINT UNSIGNED NOT NULL,
    reading DECIMAL(10,2) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meter_id) REFERENCES meters(id)
);
```

#### Таблица bills

```sql
CREATE TABLE bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    due_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id)
);
```

#### Таблица payments

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscriber_id BIGINT UNSIGNED NOT NULL,
    bill_id BIGINT UNSIGNED,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('cash', 'card', 'transfer') NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subscriber_id) REFERENCES subscribers(id),
    FOREIGN KEY (bill_id) REFERENCES bills(id)
);
```

#### Таблица tariffs

```sql
CREATE TABLE tariffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('water', 'electricity', 'gas') NOT NULL,
    rate DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Таблица tax_rates

```sql
CREATE TABLE tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    rate DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Таблица tax_reports

```sql
CREATE TABLE tax_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 2. Основные запросы

### 2.1 Работа с абонентами

```sql
-- Получение списка активных абонентов
SELECT * FROM subscribers WHERE status = 'active';

-- Получение детальной информации об абоненте
SELECT s.*, 
       COUNT(DISTINCT sv.id) as services_count,
       SUM(b.amount) as total_bills,
       SUM(p.amount) as total_payments
FROM subscribers s
LEFT JOIN services sv ON s.id = sv.subscriber_id
LEFT JOIN bills b ON s.id = b.subscriber_id
LEFT JOIN payments p ON s.id = p.subscriber_id
WHERE s.id = ?
GROUP BY s.id;
```

### 2.2 Работа со счетчиками

```sql
-- Получение последних показаний счетчика
SELECT mr.*, m.number, m.type
FROM meter_readings mr
JOIN meters m ON mr.meter_id = m.id
WHERE m.id = ?
ORDER BY mr.date DESC
LIMIT 1;

-- Получение истории показаний
SELECT mr.*, m.number, m.type
FROM meter_readings mr
JOIN meters m ON mr.meter_id = m.id
WHERE m.id = ?
ORDER BY mr.date DESC;
```

### 2.3 Работа со счетами

```sql
-- Получение статистики по счетам
SELECT 
    COUNT(*) as total_bills,
    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_bills,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bills,
    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_bills,
    SUM(amount) as total_amount
FROM bills
WHERE created_at BETWEEN ? AND ?;
```

### 2.4 Работа с налогами

```sql
-- Получение отчета по налогам
SELECT 
    tr.*,
    COUNT(DISTINCT b.id) as bills_count,
    SUM(b.amount) as total_amount
FROM tax_reports tr
LEFT JOIN bills b ON b.created_at BETWEEN tr.period_start AND tr.period_end
WHERE tr.id = ?
GROUP BY tr.id;
```

## 3. Индексы

### 3.1 Центральная база данных

```sql
CREATE INDEX idx_tenants_domain ON tenants(domain);
CREATE INDEX idx_tenants_database ON tenants(database);
CREATE INDEX idx_users_tenant ON users(tenant_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_history_tenant ON history(tenant_id);
CREATE INDEX idx_history_user ON history(user_id);
CREATE INDEX idx_history_created ON history(created_at);
```

### 3.2 База данных клиента

```sql
CREATE INDEX idx_subscribers_status ON subscribers(status);
CREATE INDEX idx_subscribers_phone ON subscribers(phone);
CREATE INDEX idx_subscribers_email ON subscribers(email);
CREATE INDEX idx_services_subscriber ON services(subscriber_id);
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_meters_service ON meters(service_id);
CREATE INDEX idx_meters_number ON meters(number);
CREATE INDEX idx_meter_readings_meter ON meter_readings(meter_id);
CREATE INDEX idx_meter_readings_date ON meter_readings(date);
CREATE INDEX idx_bills_subscriber ON bills(subscriber_id);
CREATE INDEX idx_bills_status ON bills(status);
CREATE INDEX idx_bills_due_date ON bills(due_date);
CREATE INDEX idx_payments_subscriber ON payments(subscriber_id);
CREATE INDEX idx_payments_bill ON payments(bill_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_tariffs_type ON tariffs(type);
CREATE INDEX idx_tax_reports_period ON tax_reports(period_start, period_end);
CREATE INDEX idx_tax_reports_status ON tax_reports(status);
```

## 4. Триггеры

### 4.1 Обновление баланса абонента

```sql
DELIMITER //

CREATE TRIGGER after_payment_insert
AFTER INSERT ON payments
FOR EACH ROW
BEGIN
    UPDATE subscribers 
    SET balance = balance - NEW.amount
    WHERE id = NEW.subscriber_id;
END//

CREATE TRIGGER after_payment_delete
AFTER DELETE ON payments
FOR EACH ROW
BEGIN
    UPDATE subscribers 
    SET balance = balance + OLD.amount
    WHERE id = OLD.subscriber_id;
END//

CREATE TRIGGER after_payment_update
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    UPDATE subscribers 
    SET balance = balance + OLD.amount - NEW.amount
    WHERE id = NEW.subscriber_id;
END//

DELIMITER ;
```

### 4.2 Логирование изменений

```sql
DELIMITER //

CREATE TRIGGER after_subscriber_update
AFTER UPDATE ON subscribers
FOR EACH ROW
BEGIN
    INSERT INTO history (tenant_id, user_id, action, details)
    VALUES (
        NEW.tenant_id,
        NEW.updated_by,
        'subscriber_updated',
        JSON_OBJECT(
            'subscriber_id', NEW.id,
            'old_status', OLD.status,
            'new_status', NEW.status,
            'old_balance', OLD.balance,
            'new_balance', NEW.balance
        )
    );
END//

DELIMITER ;
```
