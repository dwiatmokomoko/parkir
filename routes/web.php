<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::middleware(['web'])->group(function () {
    // Login
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    // Protected Admin Routes
    Route::middleware(['auth:web', 'admin'])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.index');
        })->name('dashboard');

        Route::get('/transactions', function () {
            return view('transactions.index');
        })->name('transactions.index');

        Route::get('/attendants', function () {
            return view('attendants.index');
        })->name('attendants.index');

        Route::get('/rates', function () {
            return view('rates.index');
        })->name('rates.index');

        Route::get('/reports', function () {
            return view('reports.index');
        })->name('reports.index');

        Route::get('/audit-logs', function () {
            return view('audit-logs.index');
        })->name('audit-logs.index');

        Route::post('/logout', function () {
            auth()->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/login');
        })->name('logout');
    });
});

// Attendant Routes
Route::prefix('attendant')->middleware(['web'])->group(function () {
    // Login
    Route::get('/login', function () {
        return view('attendant.login');
    })->name('attendant.login');

    // Protected Attendant Routes
    Route::middleware(['auth:attendant', 'attendant'])->group(function () {
        Route::get('/generate', function () {
            return view('attendant.generate');
        })->name('attendant.generate');

        Route::post('/logout', function () {
            auth('attendant')->logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect('/attendant/login');
        })->name('attendant.logout');
    });
});
