# Parking Payment Monitoring System

Sistem Monitoring Pembayaran Parkir adalah platform berbasis payment gateway yang dirancang untuk mencatat, memproses, dan memantau transaksi pembayaran parkir non-tunai secara real-time.

## Fitur Utama

- ✅ Integrasi Payment Gateway (Midtrans)
- ✅ Pembuatan QR Code untuk pembayaran
- ✅ Dashboard monitoring real-time
- ✅ Manajemen juru parkir
- ✅ Konfigurasi tarif parkir
- ✅ Laporan transaksi (PDF/Excel)
- ✅ Audit logging lengkap
- ✅ Notifikasi real-time
- ✅ Property-based testing
- ✅ Enkripsi data sensitif

## Tech Stack

- **Backend**: Laravel 10.x (PHP 8.2+)
- **Database**: PostgreSQL 15+
- **Frontend**: Blade Templates + Tailwind CSS 3.x + Alpine.js
- **Payment Gateway**: Midtrans (QRIS, GoPay, OVO, DANA, Virtual Account)
- **PDF Generation**: DomPDF
- **Excel Export**: Laravel Excel (PhpSpreadsheet)
- **Property-Based Testing**: Eris
- **Cache & Queue**: Redis
- **Web Server**: Nginx
- **Process Manager**: Supervisor

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ & NPM
- PostgreSQL 15+
- Redis 6+
- Nginx
- Supervisor (untuk production)

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd parking-payment-system
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

```bash
# Create database
createdb parking_payment_db

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### 5. Build Frontend Assets

```bash
npm run build
```

### 6. Start Development Server

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite development server
npm run dev

# Terminal 3: Start queue worker
php artisan queue:work
```

## Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=parking_payment_db
DB_USERNAME=postgres
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Midtrans
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false

# Session
ADMIN_SESSION_LIFETIME=30
ATTENDANT_SESSION_LIFETIME=15
```

## Usage

### Admin Dashboard

1. Access: `http://localhost:8000/login`
2. Login dengan credentials admin
3. Akses dashboard untuk monitoring transaksi

### Juru Parkir Interface

1. Access: `http://localhost:8000/attendant/login`
2. Login dengan nomor registrasi dan PIN
3. Buat QR code untuk pembayaran parkir

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Unit Tests

```bash
php artisan test --testsuite=Unit
```

### Run Feature Tests

```bash
php artisan test --testsuite=Feature
```

### Run Property-Based Tests

```bash
php artisan test --filter PropertyTest
```

### Generate Coverage Report

```bash
php artisan test --coverage
```

## API Documentation

Lihat [API_ROUTES_DOCUMENTATION.md](API_ROUTES_DOCUMENTATION.md) untuk dokumentasi lengkap API endpoints.

## User Documentation

- [Admin User Guide](USER_GUIDE_ADMIN.md) - Panduan untuk admin Dishub
- [Attendant User Guide](USER_GUIDE_ATTENDANT.md) - Panduan untuk juru parkir

## Technical Documentation

Lihat [TECHNICAL_DOCUMENTATION.md](TECHNICAL_DOCUMENTATION.md) untuk dokumentasi teknis sistem.

## Deployment

Lihat [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) untuk panduan deployment ke production.

## Project Structure

```
parking-payment-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   ├── Jobs/
│   ├── Observers/
│   └── Repositories/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── Generators/
├── config/
├── storage/
├── public/
└── bootstrap/
```

## Database Schema

### Main Tables

- **users**: Admin Dishub
- **parking_attendants**: Juru parkir
- **parking_rates**: Tarif parkir
- **transactions**: Transaksi pembayaran
- **audit_logs**: Audit trail
- **notifications**: Notifikasi
- **reports**: Generated reports

Lihat [TECHNICAL_DOCUMENTATION.md](TECHNICAL_DOCUMENTATION.md) untuk ERD lengkap.

## Security

### Implemented Security Measures

- ✅ HTTPS/TLS encryption
- ✅ Password hashing (bcrypt)
- ✅ Session management dengan timeout
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention
- ✅ Rate limiting
- ✅ Audit logging
- ✅ Data encryption at rest
- ✅ Webhook signature verification

## Performance

### Optimization Techniques

- Database indexing
- Query caching dengan Redis
- Session caching
- Asset minification
- Lazy loading
- Connection pooling
- Gzip compression

### Performance Targets

- API response time: < 200ms
- Dashboard load time: < 2s
- Report generation: < 10s (untuk 10,000 transactions)
- Concurrent users: 100+

## Monitoring & Logging

### Logging

- Application logs: `/storage/logs/`
- Daily rotation
- 30 days retention
- Log level: debug (development), warning (production)

### Monitoring

- Database query monitoring
- Application performance monitoring (optional: Sentry)
- Error tracking
- Health checks

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check PostgreSQL is running
   - Verify database credentials in .env
   - Check database exists

2. **Queue Workers Not Processing**
   - Check Redis is running
   - Verify queue connection in .env
   - Check supervisor status (production)

3. **Payment Gateway Integration Issues**
   - Verify Midtrans credentials
   - Check webhook configuration
   - Test with sandbox environment first

Lihat [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) untuk troubleshooting lebih lengkap.

## Contributing

1. Create feature branch: `git checkout -b feature/your-feature`
2. Commit changes: `git commit -am 'Add your feature'`
3. Push to branch: `git push origin feature/your-feature`
4. Submit pull request

## Code Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments untuk complex logic
- Write tests untuk new features
- Run linter: `php artisan pint`

## Support

- **Email**: support@dishub.go.id
- **Telepon**: 021-1234567
- **Jam Kerja**: Senin-Jumat, 08:00-17:00 WIB

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Changelog

### Version 1.0 (2024-01-15)

- Initial release
- Core payment processing
- Admin dashboard
- Juru parkir interface
- Reporting system
- Audit logging
- Property-based testing

---

**Last Updated**: 2024-01-15
**Version**: 1.0

6. Configure your `.env` file:
   - Set PostgreSQL database credentials
   - Set Midtrans API keys (get from Midtrans dashboard)
   - Configure Redis connection
   - Set session timeouts

7. Create PostgreSQL database
```bash
createdb parking_payment_db
```

8. Run migrations
```bash
php artisan migrate
```

9. Seed the database (optional)
```bash
php artisan db:seed
```

10. Build frontend assets
```bash
npm run dev
```

11. Start the development server
```bash
php artisan serve
```

12. Start the queue worker (in a separate terminal)
```bash
php artisan queue:work
```

## Configuration

### Database Configuration
Update these values in `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=parking_payment_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Midtrans Configuration
Get your API keys from [Midtrans Dashboard](https://dashboard.midtrans.com/):
```
MIDTRANS_SERVER_KEY=your-server-key-here
MIDTRANS_CLIENT_KEY=your-client-key-here
MIDTRANS_IS_PRODUCTION=false
```

### Redis Configuration
```
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Session Timeout
- Admin session: 30 minutes (configurable via `ADMIN_SESSION_LIFETIME`)
- Attendant session: 15 minutes (configurable via `ATTENDANT_SESSION_LIFETIME`)

## Testing

Run the test suite:
```bash
php artisan test
```

Run property-based tests:
```bash
php artisan test --group=property
```

## Features

- **Payment Gateway Integration**: Support for QRIS, e-wallet, and Virtual Account
- **QR Code Generation**: Generate unique QR codes for parking payments
- **Real-time Monitoring**: Dashboard with live transaction updates
- **Report Generation**: Export reports in PDF and Excel formats
- **Parking Attendant Management**: Manage parking attendant profiles
- **Audit Logging**: Complete audit trail for all system activities
- **Notifications**: Real-time notifications for successful payments

## Security

- HTTPS enforcement
- Password hashing with bcrypt
- Session timeout management
- CSRF protection
- Input validation and sanitization
- Audit logging
- Rate limiting

## License

This project is proprietary software developed for Dinas Perhubungan.

## Support

For support, please contact the development team or refer to the technical documentation.
