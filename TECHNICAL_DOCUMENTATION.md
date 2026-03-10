# Dokumentasi Teknis - Sistem Monitoring Pembayaran Parkir

## Daftar Isi

1. [Arsitektur Sistem](#arsitektur-sistem)
2. [Stack Teknologi](#stack-teknologi)
3. [Struktur Database](#struktur-database)
4. [API Endpoints](#api-endpoints)
5. [Integrasi Payment Gateway](#integrasi-payment-gateway)
6. [Keamanan](#keamanan)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring dan Logging](#monitoring-dan-logging)
9. [Disaster Recovery](#disaster-recovery)

## Arsitektur Sistem

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Client Layer                            │
├──────────────────────┬──────────────────────┬────────────────┤
│  Admin Dashboard     │  Juru Parkir UI      │  Mobile App    │
│  (Blade + Tailwind)  │  (Blade + Tailwind)  │  (QRIS)        │
└──────────────────────┴──────────────────────┴────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Application Layer                          │
├──────────────────────────────────────────────────────────────┤
│  Laravel 10.x Backend API                                    │
│  - Authentication Service                                    │
│  - Payment Service                                           │
│  - Transaction Service                                       │
│  - Report Service                                            │
│  - Notification Service                                      │
└──────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┼─────────────┐
                ▼             ▼             ▼
        ┌──────────────┐ ┌──────────┐ ┌──────────┐
        │ PostgreSQL   │ │  Redis   │ │ Midtrans │
        │  Database    │ │  Cache   │ │ Gateway  │
        └──────────────┘ └──────────┘ └──────────┘
```

### Component Interaction

1. **Client Layer**: User interfaces untuk admin dan juru parkir
2. **Application Layer**: Laravel backend yang menangani business logic
3. **Data Layer**: PostgreSQL untuk persistent storage, Redis untuk caching
4. **Integration Layer**: Midtrans untuk payment processing

## Stack Teknologi

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.2+
- **Database**: PostgreSQL 15+
- **Cache**: Redis 6+
- **Queue**: Laravel Queue dengan Redis driver

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js
- **Charts**: Chart.js
- **PDF Generation**: DomPDF
- **Excel Export**: Laravel Excel (PhpSpreadsheet)

### Infrastructure
- **Web Server**: Nginx
- **Application Server**: PHP-FPM
- **Process Manager**: Supervisor
- **SSL/TLS**: Let's Encrypt

### Testing
- **Unit Testing**: PHPUnit
- **Property-Based Testing**: Eris
- **Mocking**: Mockery

## Struktur Database

### Entity Relationship Diagram

```
users (1) ──────────────── (M) audit_logs
  │
  └─ (1) ──────────────── (M) parking_rates

parking_attendants (1) ──────────────── (M) transactions
  │                                          │
  └─ (1) ──────────────── (M) notifications ─┘

transactions (1) ──────────────── (M) audit_logs

reports (M) ──────────────── (1) users
```

### Tabel Utama

#### users
```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    is_active BOOLEAN DEFAULT true,
    last_login_at TIMESTAMP,
    last_login_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### parking_attendants
```sql
CREATE TABLE parking_attendants (
    id BIGSERIAL PRIMARY KEY,
    registration_number VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    street_section VARCHAR(255) NOT NULL,
    location_side VARCHAR(50),
    bank_account_number TEXT,
    bank_name VARCHAR(100),
    pin TEXT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### transactions
```sql
CREATE TABLE transactions (
    id BIGSERIAL PRIMARY KEY,
    transaction_id VARCHAR(100) UNIQUE NOT NULL,
    parking_attendant_id BIGINT NOT NULL REFERENCES parking_attendants(id),
    street_section VARCHAR(255) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status VARCHAR(50) NOT NULL,
    qr_code_data TEXT,
    qr_code_generated_at TIMESTAMP,
    qr_code_expires_at TIMESTAMP,
    paid_at TIMESTAMP,
    failure_reason TEXT,
    retry_count INT DEFAULT 0,
    midtrans_transaction_id VARCHAR(255),
    midtrans_response JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Indexes

Untuk performa optimal, berikut indexes yang digunakan:

```sql
-- users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_is_active ON users(is_active);

-- parking_attendants
CREATE INDEX idx_attendants_registration ON parking_attendants(registration_number);
CREATE INDEX idx_attendants_street_section ON parking_attendants(street_section);
CREATE INDEX idx_attendants_is_active ON parking_attendants(is_active);

-- transactions
CREATE INDEX idx_transactions_transaction_id ON transactions(transaction_id);
CREATE INDEX idx_transactions_attendant_id ON transactions(parking_attendant_id);
CREATE INDEX idx_transactions_street_section ON transactions(street_section);
CREATE INDEX idx_transactions_payment_status ON transactions(payment_status);
CREATE INDEX idx_transactions_created_at ON transactions(created_at);
CREATE INDEX idx_transactions_paid_at ON transactions(paid_at);

-- audit_logs
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_entity_type ON audit_logs(entity_type);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
```

## API Endpoints

### Authentication

```
POST   /api/auth/login              - Admin login
POST   /api/auth/logout             - Admin logout
GET    /api/auth/check              - Check session validity

POST   /api/attendant/auth/login    - Attendant login
POST   /api/attendant/auth/logout   - Attendant logout
```

### Payments

```
POST   /api/payments/generate-qr    - Generate QR code
POST   /api/payments/callback       - Midtrans webhook
POST   /api/payments/retry/{id}     - Retry failed payment
GET    /api/payments/status/{id}    - Get payment status
```

### Transactions

```
GET    /api/transactions                    - List transactions
GET    /api/transactions/{id}               - Get transaction details
GET    /api/transactions/location/{section} - Get by location
GET    /api/transactions/attendant/{id}     - Get by attendant
```

### Dashboard

```
GET    /api/dashboard                       - Dashboard summary
GET    /api/dashboard/daily-revenue         - Daily revenue (30 days)
GET    /api/dashboard/monthly-revenue       - Monthly revenue (12 months)
GET    /api/dashboard/location-stats        - Statistics by location
GET    /api/dashboard/attendant-stats       - Statistics by attendant
GET    /api/dashboard/vehicle-stats         - Statistics by vehicle type
```

### Reports

```
POST   /api/reports/generate        - Generate report (async)
GET    /api/reports/{id}/download   - Download report
GET    /api/reports/{id}/status     - Check report status
```

### Parking Attendants

```
GET    /api/attendants              - List attendants
POST   /api/attendants              - Create attendant
GET    /api/attendants/{id}         - Get attendant details
PUT    /api/attendants/{id}         - Update attendant
POST   /api/attendants/{id}/activate   - Activate attendant
POST   /api/attendants/{id}/deactivate - Deactivate attendant
```

### Parking Rates

```
GET    /api/rates                   - Get all rates
PUT    /api/rates                   - Update rates
GET    /api/rates/location/{section} - Get rates by location
```

### Audit Logs

```
GET    /api/audit-logs              - List audit logs
GET    /api/audit-logs/search       - Search audit logs
```

### Notifications

```
GET    /api/attendant/notifications - Get notifications
POST   /api/attendant/notifications/{id}/read - Mark as read
```

## Integrasi Payment Gateway

### Midtrans Integration

#### Configuration

```php
// config/midtrans.php
return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized' => true,
    'is_3ds' => true,
];
```

#### Payment Flow

1. **Generate QR Code**
   - Create transaction record dengan status "pending"
   - Call Midtrans API untuk create Snap transaction
   - Generate QR code dari Snap token
   - Return QR code ke client

2. **Payment Processing**
   - User scan QR code
   - User select payment method
   - Midtrans process payment
   - Midtrans send webhook notification

3. **Webhook Handling**
   - Verify webhook signature
   - Update transaction status
   - Create notification
   - Log audit trail

#### Webhook Signature Verification

```php
$serverKey = config('midtrans.server_key');
$orderId = $notification['order_id'];
$statusCode = $notification['status_code'];
$grossAmount = $notification['gross_amount'];

$signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if ($signatureKey !== $notification['signature_key']) {
    throw new Exception('Invalid signature');
}
```

## Keamanan

### Authentication & Authorization

1. **Session-based Authentication**
   - Admin: 30 minutes timeout
   - Attendant: 15 minutes timeout
   - Automatic logout on inactivity

2. **Password Security**
   - Bcrypt hashing
   - Minimum 8 characters
   - Regular change required

3. **Role-based Access Control**
   - Admin role untuk admin endpoints
   - Attendant role untuk attendant endpoints

### Data Security

1. **Encryption**
   - HTTPS/TLS untuk semua komunikasi
   - Sensitive fields encrypted at rest (bank account, PIN)
   - Environment variables untuk credentials

2. **Input Validation**
   - Laravel Form Requests
   - XSS prevention dengan Blade escaping
   - CSRF protection

3. **SQL Injection Prevention**
   - Eloquent ORM dengan parameterized queries
   - No raw SQL queries

### Rate Limiting

```php
// config/rate-limiting.php
- Public endpoints: 60 requests/minute per IP
- Login endpoints: 5 attempts/15 minutes per IP
- QR generation: 10 per minute per attendant
```

## Performance Optimization

### Database Optimization

1. **Query Optimization**
   - Eager loading dengan `with()`
   - Proper indexing
   - Query caching dengan Redis

2. **Connection Pooling**
   - PgBouncer untuk PostgreSQL
   - Connection reuse

### Caching Strategy

1. **Query Caching**
   - Cache parking rates (1 hour)
   - Cache attendant data (30 minutes)
   - Cache dashboard statistics (5 minutes)

2. **Session Caching**
   - Redis untuk session storage
   - Distributed session support

### Frontend Optimization

1. **Asset Minification**
   - CSS minification
   - JavaScript minification
   - Image optimization

2. **Lazy Loading**
   - Images lazy loaded
   - Charts rendered on demand

## Monitoring dan Logging

### Application Logging

```php
// config/logging.php
- Channel: stack
- Daily rotation
- 30 days retention
- Log level: warning (production)
```

### Error Tracking

```php
// Optional: Sentry integration
- Real-time error notifications
- Error grouping
- Performance monitoring
```

### Database Monitoring

```sql
-- Slow query log
log_min_duration_statement = 1000  -- 1 second

-- Query statistics
SELECT query, calls, mean_time FROM pg_stat_statements
ORDER BY mean_time DESC LIMIT 10;
```

## Disaster Recovery

### Backup Strategy

1. **Database Backups**
   - Daily automated backups
   - 30-day retention
   - Encrypted storage
   - S3 upload (optional)

2. **Application Code**
   - Git repository
   - Daily commits
   - Branch protection

### Recovery Procedure

1. **RTO**: 1 hour untuk critical systems
2. **RPO**: 1 hour untuk database, 15 minutes untuk code
3. **Restore Steps**:
   - Restore database dari backup
   - Restore code dari git
   - Run migrations
   - Verify data integrity
   - Restart services

---

**Versi**: 1.0
**Terakhir Diperbarui**: 2024-01-15
