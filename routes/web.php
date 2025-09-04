<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AuthController;

// Página principal do portal cativo
Route::get('/', [PortalController::class, 'index'])->name('portal.index');

// Rotas de Autenticação
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/create-admin', [AuthController::class, 'createAdmin'])->name('create.admin');

// Página de acesso administrativo
Route::get('/admin-access', function () {
    return view('admin-access');
})->name('admin.access');

// Painel Administrativo (Protegido por autenticação)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.access'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/revenue-report', [AdminController::class, 'revenueReport'])->name('revenue-report');
    Route::get('/vouchers', [AdminController::class, 'vouchers'])->name('vouchers');
    Route::post('/vouchers', [AdminController::class, 'createVoucher'])->name('vouchers.create');
    Route::delete('/vouchers/{id}', [AdminController::class, 'deactivateVoucher'])->name('vouchers.deactivate');
    Route::get('/devices', [AdminController::class, 'devices'])->name('devices');
    Route::get('/connection-logs', [AdminController::class, 'connectionLogs'])->name('connection-logs');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::get('/api/stats', [AdminController::class, 'apiStats'])->name('api.stats');
    Route::post('/export', [AdminController::class, 'exportReport'])->name('export');
});

