<?php

use App\Http\Controllers\AttendantAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
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
