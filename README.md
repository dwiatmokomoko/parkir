# Parking Payment Monitoring System

Sistem Monitoring Pembayaran Parkir adalah platform berbasis payment gateway yang dirancang untuk mencatat, memproses, dan memantau transaksi pembayaran parkir non-tunai secara real-time.

## Tech Stack

- **Backend**: Laravel 10.x (PHP 8.2+)
- **Database**: PostgreSQL 15+
- **Frontend**: Blade Templates + Tailwind CSS 3.x + Alpine.js
- **Payment Gateway**: Midtrans (QRIS, GoPay, OVO, DANA, Virtual Account)
- **PDF Generation**: DomPDF
- **Excel Export**: Laravel Excel (PhpSpreadsheet)
- **Property-Based Testing**: Eris
- **Cache & Queue**: Redis

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- PostgreSQL 15+
- Redis

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd parking-payment-system
```

2. Install PHP dependencies
```bash
composer install
```

3. Install Node dependencies
```bash
npm install
```

4. Copy environment file
```bash
cp .env.example .env
```

5. Generate application key
```bash
php artisan key:generate
```

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
