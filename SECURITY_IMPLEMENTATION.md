# Security Implementation Guide

## Overview

This document outlines the comprehensive security measures implemented in the Parking Payment Monitoring System.

## 1. HTTPS Enforcement (18.1)

### Configuration

- **Middleware**: `ForceHttps` middleware redirects all HTTP requests to HTTPS in production
- **Session Cookies**: Secure flag enabled on all session cookies
- **HSTS Headers**: HTTP Strict-Transport-Security header set with 1-year max-age

### Implementation Details

**File**: `app/Http/Middleware/ForceHttps.php`

- Redirects HTTP to HTTPS in production environment
- Adds HSTS header with `max-age=31536000; includeSubDomains; preload`
- Adds additional security headers:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: DENY`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`

### Configuration Files

- `.env`: `APP_URL=https://localhost`, `FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`
- `config/session.php`: `secure => true` (default)

## 2. Input Validation and Sanitization (18.2)

### Form Requests

All inputs are validated using Laravel Form Requests:

- `LoginRequest`: Admin login validation
- `AttendantLoginRequest`: Attendant login validation
- `GenerateQRRequest`: QR code generation validation
- `StoreAttendantRequest`: Parking attendant creation validation
- `UpdateAttendantRequest`: Parking attendant update validation
- `UpdateParkingRateRequest`: Parking rate update validation
- `GenerateReportRequest`: Report generation validation

### XSS Prevention

- All Blade templates use `{{ }}` syntax for automatic escaping
- Sensitive data is hidden from serialization using `$hidden` property in models
- User input is validated before storage

### CSRF Protection

- CSRF token validation enabled for all state-changing requests
- `VerifyCsrfToken` middleware applied to web routes
- Token included in all forms

### File Upload Validation

- Report generation validates file type (PDF/Excel)
- File size limits enforced
- Files stored in secure storage directory

## 3. Database Security (18.3)

### Encryption

**File**: `app/Models/ParkingAttendant.php`

Sensitive fields are encrypted at rest:
- `bank_account_number`: Encrypted using Laravel's `Crypt` facade
- `pin`: Encrypted using Laravel's `Crypt` facade

Encryption/decryption happens automatically via model accessors and mutators.

### Parameterized Queries

- All database queries use Eloquent ORM
- No raw SQL queries with user input
- Prepared statements prevent SQL injection

### Database Connection Security

**File**: `config/database.php`

- PostgreSQL SSL connection enabled: `sslmode = 'require'`
- Database user configured with minimal privileges
- Connection pooling configured via Redis

### Database Constraints

- Foreign key constraints enforced
- Unique constraints on registration numbers
- NOT NULL constraints on required fields
- Check constraints on valid values

## 4. Payment Security (18.4)

### Webhook Signature Verification

**File**: `app/Services/MidtransService.php`

- All Midtrans webhooks verified using SHA512 HMAC
- Signature verification uses server key
- Invalid signatures are logged and rejected

```php
public function verifyWebhookSignature(array $notificationData): bool
{
    $serverKey = config('midtrans.server_key');
    $orderId = $notificationData['order_id'];
    $statusCode = $notificationData['status_code'];
    $grossAmount = $notificationData['gross_amount'];
    
    $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
    return hash_equals($expectedSignature, $notificationData['signature_key']);
}
```

### Transaction Amount Validation

- Expected parking rate is retrieved from database
- Actual payment amount is compared with expected rate
- Mismatches are logged and rejected

### Idempotency

- Duplicate webhook notifications are detected and ignored
- Transaction status is checked before updating
- Prevents duplicate payment processing

### QR Code Security

**File**: `app/Services/QRCodeService.php`

- QR codes include HMAC signature for integrity verification
- QR codes expire after 15 minutes
- QR code data includes transaction ID, parking rate, attendant ID
- Signature verification prevents tampering

## 5. Error Handling and Logging (18.5)

### Custom Error Pages

- `resources/views/errors/401.blade.php`: Unauthorized access
- `resources/views/errors/403.blade.php`: Forbidden access
- `resources/views/errors/404.blade.php`: Not found
- `resources/views/errors/500.blade.php`: Server error

### Logging Configuration

**File**: `config/logging.php`

- Daily log rotation enabled
- Logs stored in `storage/logs/laravel.log`
- Log level configurable via `LOG_LEVEL` environment variable
- Monolog used for structured logging

### Sensitive Data Masking

**File**: `app/Exceptions/Handler.php`

Sensitive fields are masked in logs:
- `password`
- `pin`
- `bank_account_number`
- `credit_card`
- `cvv`
- `token`
- `secret`
- `api_key`
- `server_key`
- `client_key`

**File**: `app/Http/Middleware/MaskSensitiveData.php`

- All POST, PUT, DELETE requests are logged
- Sensitive input data is masked before logging
- Request IP address and user agent are logged for audit trail

### Exception Logging

- All exceptions are logged with context
- Stack traces are included for debugging
- Sensitive data is masked in exception logs

## 6. Authentication Security

### Admin Authentication

- Password hashing using bcrypt (Laravel default)
- Session timeout: 30 minutes of inactivity
- Login attempts logged with IP address and timestamp
- Rate limiting: 5 attempts per 15 minutes per IP

### Attendant Authentication

- PIN-based authentication
- Registration number + PIN combination
- Session timeout: 15 minutes of inactivity
- Rate limiting: 5 attempts per 15 minutes per IP

### Session Management

- Session driver: Redis (configured in `.env`)
- Session encryption: Enabled
- HTTP-only cookies: Enabled
- Same-site cookies: Lax (default)
- Secure flag: Enabled for HTTPS

## 7. Rate Limiting

**File**: `config/rate-limiting.php`

- API rate limiting: 60 requests per minute per IP
- Login rate limiting: 5 attempts per 15 minutes per IP
- QR code generation: 10 per minute per attendant

## 8. Audit Logging

**File**: `app/Services/AuditLogger.php`

All important actions are logged:
- Login attempts (success and failure)
- Transaction status changes
- Parking attendant profile modifications
- Parking rate changes
- Report generation
- Administrative actions

Audit logs include:
- User ID and type
- Action type
- Entity type and ID
- Old and new values
- IP address
- User agent
- Timestamp

## 9. Environment Configuration

### Sensitive Configuration

All sensitive configuration is stored in `.env` file:

```
# HTTPS Configuration
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true

# Database SSL
DB_SSLMODE=require

# Midtrans Keys
MIDTRANS_SERVER_KEY=your-server-key-here
MIDTRANS_CLIENT_KEY=your-client-key-here
MIDTRANS_IS_PRODUCTION=false

# Session Timeouts
ADMIN_SESSION_LIFETIME=30
ATTENDANT_SESSION_LIFETIME=15
```

### Environment Variables

- Never commit `.env` file to version control
- Use `.env.example` for template
- Rotate sensitive keys regularly

## 10. Deployment Recommendations

### Web Server Configuration (Nginx)

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name example.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS Configuration
server {
    listen 443 ssl http2;
    server_name example.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
}
```

### Database Security

- Use strong passwords for database users
- Restrict database access to application server only
- Enable SSL connections to database
- Regular backups with encryption
- Separate read-only user for reports

### File Permissions

```bash
# Laravel storage and cache directories
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Sensitive files
chmod 600 .env
chmod 600 config/midtrans.php
```

### Backup Strategy

- Daily automated backups
- Backup encryption enabled
- 30-day retention policy
- Regular restore testing
- Off-site backup storage

## 11. Security Checklist

- [ ] HTTPS enabled in production
- [ ] HSTS headers configured
- [ ] Session cookies secure flag enabled
- [ ] All inputs validated using Form Requests
- [ ] CSRF protection enabled
- [ ] Sensitive fields encrypted in database
- [ ] Database SSL connection enabled
- [ ] Webhook signatures verified
- [ ] Transaction amounts validated
- [ ] Idempotency checks implemented
- [ ] Error pages customized
- [ ] Sensitive data masked in logs
- [ ] Audit logging enabled
- [ ] Rate limiting configured
- [ ] Environment variables secured
- [ ] File permissions set correctly
- [ ] Backup strategy implemented
- [ ] Security headers configured

## 12. Monitoring and Alerting

Monitor the following metrics:

- Payment success rate (alert if < 95%)
- Failed login attempts (alert if > 10 per hour)
- Database connection errors
- Webhook processing errors
- Slow database queries
- Disk space usage
- Log file size

## 13. Incident Response

In case of security incident:

1. Immediately revoke compromised credentials
2. Review audit logs for unauthorized access
3. Notify affected users
4. Rotate API keys and secrets
5. Update security patches
6. Document incident and response

## 14. Regular Security Updates

- Update Laravel framework regularly
- Update dependencies using Composer
- Monitor security advisories
- Apply security patches promptly
- Conduct regular security audits
- Perform penetration testing

## References

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Midtrans Security](https://docs.midtrans.com/en/security)
- [PostgreSQL Security](https://www.postgresql.org/docs/current/sql-syntax.html)
