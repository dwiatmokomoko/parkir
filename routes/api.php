<?php

use App\Http\Controllers\AttendantAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ParkingAttendantController;
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
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check', [AuthController::class, 'checkSession']);
});

// Attendant Authentication Routes
Route::prefix('attendant/auth')->group(function () {
    Route::post('/login', [AttendantAuthController::class, 'login']);
    Route::post('/logout', [AttendantAuthController::class, 'logout']);
    Route::get('/check', [AttendantAuthController::class, 'checkSession']);
});

// Payment Routes
Route::prefix('payments')->group(function () {
    Route::post('/generate-qr', [PaymentController::class, 'generateQRCode']);
    Route::post('/callback', [PaymentController::class, 'handleCallback']);
    Route::post('/retry/{transactionId}', [PaymentController::class, 'retryPayment']);
});

// Transaction Routes
Route::prefix('transactions')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::get('/{id}', [TransactionController::class, 'show']);
    Route::get('/location/{streetSection}', [TransactionController::class, 'getByLocation']);
    Route::get('/attendant/{attendantId}', [TransactionController::class, 'getByAttendant']);
});

// Dashboard Routes
Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/daily-revenue', [DashboardController::class, 'getDailyRevenue']);
    Route::get('/monthly-revenue', [DashboardController::class, 'getMonthlyRevenue']);
    Route::get('/location-stats', [DashboardController::class, 'getLocationStats']);
    Route::get('/attendant-stats', [DashboardController::class, 'getAttendantStats']);
    Route::get('/vehicle-stats', [DashboardController::class, 'getVehicleStats']);
});

// Report Routes
Route::middleware('admin')->prefix('reports')->group(function () {
    Route::post('/generate', [ReportController::class, 'generate']);
    Route::get('/{reportId}/status', [ReportController::class, 'status']);
    Route::get('/{reportId}/download', [ReportController::class, 'download']);
});

// Parking Attendant Routes
Route::middleware('admin')->prefix('attendants')->group(function () {
    Route::get('/', [ParkingAttendantController::class, 'index']);
    Route::post('/', [ParkingAttendantController::class, 'store']);
    Route::get('/{id}', [ParkingAttendantController::class, 'show']);
    Route::put('/{id}', [ParkingAttendantController::class, 'update']);
    Route::post('/{id}/activate', [ParkingAttendantController::class, 'activate']);
    Route::post('/{id}/deactivate', [ParkingAttendantController::class, 'deactivate']);
});

// Parking Rate Routes
Route::middleware('admin')->prefix('rates')->group(function () {
    Route::get('/', [\App\Http\Controllers\ParkingRateController::class, 'index']);
    Route::put('/', [\App\Http\Controllers\ParkingRateController::class, 'update']);
    Route::get('/location/{streetSection}', [\App\Http\Controllers\ParkingRateController::class, 'getByLocation']);
});
