<?php

use App\Http\Controllers\AttendantAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ParkingAttendantController;
use App\Http\Controllers\ParkingRateController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Rate Limiting Configuration:
| - Public endpoints: 60 requests per minute
| - Login endpoints: 5 attempts per 15 minutes per IP
| - QR code generation: 10 per minute per attendant
|
*/

// ============================================================================
// 17.1 Authentication Routes
// ============================================================================

// Admin Authentication Routes (5 login attempts per 15 minutes)
Route::prefix('auth')->middleware('throttle:5,15')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check', [AuthController::class, 'checkSession']);
});

// Attendant Authentication Routes (5 login attempts per 15 minutes)
Route::prefix('attendant/auth')->middleware('throttle:5,15')->group(function () {
    Route::post('/login', [AttendantAuthController::class, 'login']);
    Route::post('/logout', [AttendantAuthController::class, 'logout']);
    Route::get('/check', [AttendantAuthController::class, 'checkSession']);
});

// ============================================================================
// 17.2 Payment Routes
// ============================================================================

Route::prefix('payments')->group(function () {
    // Generate QR code (10 per minute per attendant)
    Route::post('/generate-qr', [PaymentController::class, 'generateQRCode'])
        ->middleware('throttle:10,1');
    
    // Midtrans webhook callback (no rate limiting)
    Route::post('/callback', [PaymentController::class, 'handleCallback']);
    
    // Retry failed payment (protected by attendant middleware)
    Route::post('/retry/{transactionId}', [PaymentController::class, 'retryPayment'])
        ->middleware('attendant');
    
    // Get payment status (protected by attendant middleware)
    Route::get('/status/{transactionId}', [PaymentController::class, 'getStatus'])
        ->middleware('attendant');
});

// ============================================================================
// 17.3 Transaction Routes (Admin only)
// ============================================================================

Route::prefix('transactions')->middleware('admin')->group(function () {
    // List all transactions with pagination
    Route::get('/', [TransactionController::class, 'index']);
    
    // Get transaction details
    Route::get('/{id}', [TransactionController::class, 'show']);
    
    // Get transactions by location
    Route::get('/location/{streetSection}', [TransactionController::class, 'getByLocation']);
    
    // Get transactions by attendant
    Route::get('/attendant/{attendantId}', [TransactionController::class, 'getByAttendant']);
});

// ============================================================================
// 17.4 Dashboard Routes (Admin only)
// ============================================================================

Route::prefix('dashboard')->middleware('admin')->group(function () {
    // Dashboard summary
    Route::get('/', [DashboardController::class, 'index']);
    
    // Daily revenue (last 30 days)
    Route::get('/daily-revenue', [DashboardController::class, 'getDailyRevenue']);
    
    // Monthly revenue (last 12 months)
    Route::get('/monthly-revenue', [DashboardController::class, 'getMonthlyRevenue']);
    
    // Location statistics
    Route::get('/location-stats', [DashboardController::class, 'getLocationStats']);
    
    // Attendant statistics
    Route::get('/attendant-stats', [DashboardController::class, 'getAttendantStats']);
    
    // Vehicle type statistics
    Route::get('/vehicle-stats', [DashboardController::class, 'getVehicleStats']);
});

// ============================================================================
// 17.5 Report Routes (Admin only)
// ============================================================================

Route::prefix('reports')->middleware('admin')->group(function () {
    // Generate report (async)
    Route::post('/generate', [ReportController::class, 'generate']);
    
    // Check report generation status
    Route::get('/{reportId}/status', [ReportController::class, 'status']);
    
    // Download generated report
    Route::get('/{reportId}/download', [ReportController::class, 'download']);
});

// ============================================================================
// 17.6 Parking Attendant Routes (Admin only)
// ============================================================================

Route::prefix('attendants')->middleware('admin')->group(function () {
    // List all attendants
    Route::get('/', [ParkingAttendantController::class, 'index']);
    
    // Create new attendant
    Route::post('/', [ParkingAttendantController::class, 'store']);
    
    // Get attendant details
    Route::get('/{id}', [ParkingAttendantController::class, 'show']);
    
    // Update attendant
    Route::put('/{id}', [ParkingAttendantController::class, 'update']);
    
    // Activate attendant
    Route::post('/{id}/activate', [ParkingAttendantController::class, 'activate']);
    
    // Deactivate attendant
    Route::post('/{id}/deactivate', [ParkingAttendantController::class, 'deactivate']);
});

// ============================================================================
// 17.7 Parking Rate Routes (Admin only)
// ============================================================================

Route::prefix('rates')->middleware('admin')->group(function () {
    // Get all parking rates
    Route::get('/', [ParkingRateController::class, 'index']);
    
    // Update parking rates
    Route::put('/', [ParkingRateController::class, 'update']);
    
    // Get rates by location
    Route::get('/location/{streetSection}', [ParkingRateController::class, 'getByLocation']);
});

// ============================================================================
// 17.8 Audit Log Routes (Admin only)
// ============================================================================

Route::prefix('audit-logs')->middleware('admin')->group(function () {
    // List audit logs with pagination
    Route::get('/', [AuditController::class, 'index']);
    
    // Search audit logs with filters
    Route::get('/search', [AuditController::class, 'search']);
});

// ============================================================================
// 17.9 Notification Routes (Attendant only)
// ============================================================================

Route::prefix('attendant/notifications')->middleware('attendant')->group(function () {
    // Get attendant notifications
    Route::get('/', [NotificationController::class, 'index']);
    
    // Get unread notifications
    Route::get('/unread', [NotificationController::class, 'getUnread']);
    
    // Mark notification as read
    Route::post('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    
    // Mark all notifications as read
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
