# API Routes and Middleware Documentation

## Overview

This document describes all API endpoints, their middleware protection, and rate limiting configuration for the Parking Payment Monitoring System.

## Middleware Protection

### AdminMiddleware
- **Purpose**: Protects admin-only endpoints
- **Session Timeout**: 30 minutes of inactivity
- **Checks**:
  - Valid session with `admin_user_id`
  - User exists and is active
  - Session not expired
- **Response on Failure**: 401 Unauthorized

### AttendantMiddleware
- **Purpose**: Protects attendant-only endpoints
- **Session Timeout**: 15 minutes of inactivity
- **Checks**:
  - Valid session with `attendant_user_id`
  - Attendant exists and is active
  - Session not expired
- **Response on Failure**: 401 Unauthorized

### Rate Limiting (Throttle Middleware)
- **Format**: `throttle:requests,minutes`
- **Applied to**:
  - Authentication endpoints: 5 attempts per 15 minutes per IP
  - QR code generation: 10 per minute per attendant
  - Public endpoints: 60 requests per minute per IP

## API Endpoints

### 17.1 Authentication Routes

#### Admin Authentication

**POST /api/auth/login**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Admin login endpoint
- **Request Body**:
  ```json
  {
    "email": "admin@example.com",
    "password": "password"
  }
  ```
- **Response**: Session token and user data
- **Requirements**: 6.1, 6.2

**POST /api/auth/logout**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Admin logout endpoint
- **Response**: Success message
- **Requirements**: 6.1, 6.2

**GET /api/auth/check**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Check session validity
- **Response**: Session status
- **Requirements**: 6.1, 6.2

#### Attendant Authentication

**POST /api/attendant/auth/login**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Attendant login endpoint
- **Request Body**:
  ```json
  {
    "registration_number": "ATT001",
    "pin": "1234"
  }
  ```
- **Response**: Session token and attendant data
- **Requirements**: 6.1, 6.2

**POST /api/attendant/auth/logout**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Attendant logout endpoint
- **Response**: Success message
- **Requirements**: 6.1, 6.2

**GET /api/attendant/auth/check**
- **Rate Limit**: 5 attempts per 15 minutes per IP
- **Middleware**: `throttle:5,15`
- **Description**: Check attendant session validity
- **Response**: Session status
- **Requirements**: 6.1, 6.2

---

### 17.2 Payment Routes

**POST /api/payments/generate-qr**
- **Rate Limit**: 10 per minute per attendant
- **Middleware**: `throttle:10,1`
- **Description**: Generate QR code for payment
- **Request Body**:
  ```json
  {
    "vehicle_type": "motorcycle",
    "attendant_id": 1
  }
  ```
- **Response**: QR code image and transaction data
- **Requirements**: 2.2, 3.1

**POST /api/payments/callback**
- **Rate Limit**: None (webhook endpoint)
- **Middleware**: None
- **Description**: Midtrans webhook callback for payment notifications
- **Request Body**: Midtrans notification payload
- **Response**: Success acknowledgment
- **Requirements**: 3.1, 3.3

**POST /api/payments/retry/{transactionId}**
- **Rate Limit**: Inherited from attendant middleware
- **Middleware**: `attendant`
- **Description**: Retry failed payment
- **Response**: New QR code and transaction data
- **Requirements**: 10.1

**GET /api/payments/status/{transactionId}**
- **Rate Limit**: Inherited from attendant middleware
- **Middleware**: `attendant`
- **Description**: Get payment status
- **Response**: Transaction status and details
- **Requirements**: 3.1

---

### 17.3 Transaction Routes (Admin Only)

**GET /api/transactions**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: List all transactions with pagination
- **Query Parameters**:
  - `page`: Page number (default: 1)
  - `per_page`: Items per page (default: 15)
  - `date_from`: Filter by start date
  - `date_to`: Filter by end date
  - `status`: Filter by payment status
- **Response**: Paginated transaction list
- **Requirements**: 5.1, 12.5

**GET /api/transactions/{id}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get transaction details
- **Response**: Transaction details
- **Requirements**: 5.1, 12.5

**GET /api/transactions/location/{streetSection}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get transactions by location
- **Query Parameters**:
  - `page`: Page number
  - `per_page`: Items per page
- **Response**: Transactions for specified location
- **Requirements**: 5.1, 12.5

**GET /api/transactions/attendant/{attendantId}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get transactions by attendant
- **Query Parameters**:
  - `page`: Page number
  - `per_page`: Items per page
- **Response**: Transactions for specified attendant
- **Requirements**: 5.1, 12.5

---

### 17.4 Dashboard Routes (Admin Only)

**GET /api/dashboard**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get dashboard summary
- **Response**: Summary statistics
- **Requirements**: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 9.1, 9.2, 9.3, 9.4, 9.5

**GET /api/dashboard/daily-revenue**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get daily revenue for last 30 days
- **Response**: Daily revenue data
- **Requirements**: 5.1, 5.2, 9.1

**GET /api/dashboard/monthly-revenue**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get monthly revenue for last 12 months
- **Response**: Monthly revenue data
- **Requirements**: 5.1, 5.3, 9.2

**GET /api/dashboard/location-stats**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get statistics by location
- **Response**: Location statistics
- **Requirements**: 5.1, 5.4, 9.3

**GET /api/dashboard/attendant-stats**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get statistics by attendant
- **Response**: Attendant statistics
- **Requirements**: 5.1, 5.5, 9.4

**GET /api/dashboard/vehicle-stats**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get statistics by vehicle type
- **Response**: Vehicle type statistics
- **Requirements**: 5.1, 5.6, 9.5

---

### 17.5 Report Routes (Admin Only)

**POST /api/reports/generate**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Generate transaction report (async)
- **Request Body**:
  ```json
  {
    "type": "pdf",
    "date_from": "2024-01-01",
    "date_to": "2024-01-31",
    "filters": {
      "location": "Jalan Sudirman",
      "attendant_id": 1
    }
  }
  ```
- **Response**: Report ID and status
- **Requirements**: 8.1, 8.4, 8.5, 8.8

**GET /api/reports/{reportId}/status**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Check report generation status
- **Response**: Report status (pending, processing, completed, failed)
- **Requirements**: 8.1, 8.4, 8.5, 8.8

**GET /api/reports/{reportId}/download**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Download generated report
- **Response**: File download
- **Requirements**: 8.1, 8.4, 8.5, 8.8

---

### 17.6 Parking Attendant Routes (Admin Only)

**GET /api/attendants**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: List all parking attendants
- **Query Parameters**:
  - `page`: Page number
  - `per_page`: Items per page
  - `search`: Search by name or registration number
- **Response**: Paginated attendant list
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

**POST /api/attendants**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Create new parking attendant
- **Request Body**:
  ```json
  {
    "registration_number": "ATT001",
    "name": "John Doe",
    "street_section": "Jalan Sudirman",
    "location_side": "North",
    "bank_account_number": "1234567890",
    "bank_name": "BCA",
    "pin": "1234"
  }
  ```
- **Response**: Created attendant data
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

**GET /api/attendants/{id}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get attendant details
- **Response**: Attendant data
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

**PUT /api/attendants/{id}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Update attendant
- **Request Body**: Attendant fields to update
- **Response**: Updated attendant data
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

**POST /api/attendants/{id}/activate**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Activate attendant
- **Response**: Updated attendant data
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

**POST /api/attendants/{id}/deactivate**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Deactivate attendant
- **Response**: Updated attendant data
- **Requirements**: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6

---

### 17.7 Parking Rate Routes (Admin Only)

**GET /api/rates**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get all parking rates
- **Response**: List of parking rates
- **Requirements**: 15.1, 15.2, 15.5, 15.6

**PUT /api/rates**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Update parking rates
- **Request Body**:
  ```json
  {
    "rates": [
      {
        "vehicle_type": "motorcycle",
        "street_section": null,
        "rate": 5000
      },
      {
        "vehicle_type": "car",
        "street_section": null,
        "rate": 10000
      }
    ]
  }
  ```
- **Response**: Updated rates
- **Requirements**: 15.1, 15.2, 15.5, 15.6

**GET /api/rates/location/{streetSection}**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Get rates by location
- **Response**: Rates for specified location
- **Requirements**: 15.1, 15.2, 15.5, 15.6

---

### 17.8 Audit Log Routes (Admin Only)

**GET /api/audit-logs**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: List audit logs with pagination
- **Query Parameters**:
  - `page`: Page number
  - `per_page`: Items per page
  - `date_from`: Filter by start date
  - `date_to`: Filter by end date
- **Response**: Paginated audit log list
- **Requirements**: 13.6

**GET /api/audit-logs/search**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `admin`
- **Description**: Search audit logs with filters
- **Query Parameters**:
  - `action`: Filter by action type
  - `user_id`: Filter by user
  - `entity_type`: Filter by entity type
  - `date_from`: Filter by start date
  - `date_to`: Filter by end date
- **Response**: Filtered audit log list
- **Requirements**: 13.6

---

### 17.9 Notification Routes (Attendant Only)

**GET /api/attendant/notifications**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `attendant`
- **Description**: Get attendant notifications
- **Query Parameters**:
  - `page`: Page number
  - `per_page`: Items per page
  - `is_read`: Filter by read status
- **Response**: Paginated notification list
- **Requirements**: 14.5

**GET /api/attendant/notifications/unread**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `attendant`
- **Description**: Get unread notifications
- **Response**: List of unread notifications
- **Requirements**: 14.5

**POST /api/attendant/notifications/{notificationId}/read**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `attendant`
- **Description**: Mark notification as read
- **Response**: Updated notification data
- **Requirements**: 14.5

**POST /api/attendant/notifications/mark-all-read**
- **Rate Limit**: 60 per minute per IP
- **Middleware**: `attendant`
- **Description**: Mark all notifications as read
- **Response**: Success message
- **Requirements**: 14.5

---

## Rate Limiting Configuration

### 17.10 Rate Limiting Rules

| Endpoint Category | Limit | Time Window | Key |
|---|---|---|---|
| Authentication | 5 attempts | 15 minutes | IP Address |
| QR Code Generation | 10 requests | 1 minute | Attendant ID |
| Public Endpoints | 60 requests | 1 minute | IP Address |
| API Endpoints | 100 requests | 1 minute | IP Address |

### Rate Limit Response

When rate limit is exceeded:
- **HTTP Status**: 429 Too Many Requests
- **Response Body**:
  ```json
  {
    "message": "Terlalu banyak permintaan. Silakan coba beberapa saat lagi.",
    "retry_after": 60
  }
  ```

### Rate Limit Headers

All responses include rate limit headers:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Requests remaining
- `X-RateLimit-Reset`: Unix timestamp when limit resets
- `Retry-After`: Seconds to wait before retrying (on 429 response)

---

## Security Considerations

### HTTPS Enforcement
- All API endpoints must use HTTPS
- HTTP requests are redirected to HTTPS
- Secure flag is set on session cookies

### CSRF Protection
- All state-changing requests (POST, PUT, DELETE) require CSRF token
- Token is validated by `VerifyCsrfToken` middleware

### Input Validation
- All inputs are validated using Laravel Form Requests
- SQL injection is prevented using Eloquent ORM
- XSS is prevented using Blade template escaping

### Authentication & Authorization
- Admin endpoints require valid admin session (30-minute timeout)
- Attendant endpoints require valid attendant session (15-minute timeout)
- All sessions are validated on each request

### Audit Logging
- All administrative actions are logged
- All transaction events are logged
- Audit logs are immutable and retained for 7 years

---

## Error Handling

### Common Error Responses

**401 Unauthorized**
```json
{
  "success": false,
  "message": "Sesi tidak valid. Silakan login kembali."
}
```

**403 Forbidden**
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses ke resource ini."
}
```

**404 Not Found**
```json
{
  "success": false,
  "message": "Resource tidak ditemukan."
}
```

**422 Unprocessable Entity**
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**429 Too Many Requests**
```json
{
  "message": "Terlalu banyak permintaan. Silakan coba beberapa saat lagi.",
  "retry_after": 60
}
```

**500 Internal Server Error**
```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server. Silakan coba lagi nanti."
}
```

---

## Implementation Notes

### Middleware Registration
All middleware is registered in `app/Http/Kernel.php`:
- `admin`: AdminMiddleware
- `attendant`: AttendantMiddleware
- `throttle`: ThrottleRequests (Laravel built-in)

### Route Organization
Routes are organized by feature/resource:
1. Authentication routes (public)
2. Payment routes (mixed protection)
3. Transaction routes (admin only)
4. Dashboard routes (admin only)
5. Report routes (admin only)
6. Attendant management routes (admin only)
7. Rate configuration routes (admin only)
8. Audit log routes (admin only)
9. Notification routes (attendant only)

### Rate Limiting Implementation
- Uses Laravel's built-in `ThrottleRequests` middleware
- Rate limits are stored in cache (Redis recommended for production)
- Custom rate limit keys can be defined per endpoint

---

## Testing

### Manual Testing
```bash
# Test authentication rate limiting
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"wrong"}'

# Test QR code generation rate limiting
curl -X POST http://localhost:8000/api/payments/generate-qr \
  -H "Content-Type: application/json" \
  -d '{"vehicle_type":"motorcycle","attendant_id":1}'

# Test admin middleware protection
curl -X GET http://localhost:8000/api/dashboard
```

### Automated Testing
See `tests/Feature/ApiRoutesTest.php` for comprehensive test suite.

---

## References

- Laravel Rate Limiting: https://laravel.com/docs/routing#rate-limiting
- Laravel Middleware: https://laravel.com/docs/middleware
- Parking Payment System Requirements: `.kiro/specs/parking-payment-monitoring-system/requirements.md`
- Parking Payment System Design: `.kiro/specs/parking-payment-monitoring-system/design.md`
