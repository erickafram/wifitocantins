<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalAuthController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\DriverVoucherController;

// Página principal do portal cativo
Route::get('/', [PortalController::class, 'index'])->name('portal.index');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('portal.dashboard');
    Route::post('/dashboard/payments/regenerate', [PortalDashboardController::class, 'regeneratePix'])->name('portal.dashboard.payments.regenerate');
});

Route::get('/entrar', [PortalAuthController::class, 'showLogin'])->name('portal.login');
Route::post('/entrar', [PortalAuthController::class, 'login'])->name('portal.login.submit');
Route::post('/sair', [PortalAuthController::class, 'logout'])->middleware('auth')->name('portal.logout');

// Rotas de Vouchers para Motoristas (Não requer autenticação)
Route::prefix('voucher')->name('voucher.')->group(function () {
    Route::get('/ativar', [DriverVoucherController::class, 'showActivate'])->name('activate');
    Route::post('/ativar', [DriverVoucherController::class, 'activate'])->name('activate.submit');
    Route::get('/status', [DriverVoucherController::class, 'showStatus'])->name('status');
    Route::post('/status', [DriverVoucherController::class, 'checkStatus'])->name('status.check');
    Route::post('/desconectar', [DriverVoucherController::class, 'disconnect'])->name('disconnect');
});

// Rota alternativa para /portal (redireciona para raiz)
Route::get('/portal', function () {
    return redirect('/');
})->name('portal.redirect');

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
    // Rotas acessíveis para Admin e Manager
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/revenue-report', [AdminController::class, 'revenueReport'])->name('revenue-report');
    Route::get('/devices', [AdminController::class, 'devices'])->name('devices');
    Route::get('/connection-logs', [AdminController::class, 'connectionLogs'])->name('connection-logs');
    Route::get('/api/stats', [AdminController::class, 'apiStats'])->name('api.stats');
    Route::post('/export', [AdminController::class, 'exportReport'])->name('export');
    
    // Rotas de Relatórios (Admin e Manager)
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export', [ReportsController::class, 'export'])->name('reports.export');
    
    // Rotas de Vouchers (Admin e Manager)
    Route::get('/vouchers', [AdminVoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [AdminVoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{voucher}/edit', [AdminVoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [AdminVoucherController::class, 'update'])->name('vouchers.update');
    Route::post('/vouchers/{voucher}/toggle', [AdminVoucherController::class, 'toggleStatus'])->name('vouchers.toggle');
    Route::post('/vouchers/{voucher}/reset', [AdminVoucherController::class, 'resetDaily'])->name('vouchers.reset');
    Route::delete('/vouchers/{voucher}', [AdminVoucherController::class, 'destroy'])->name('vouchers.destroy');
    
    // Rotas APENAS para Administradores
    Route::middleware(['admin.only'])->group(function () {
        // Gerenciamento de Usuários
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::get('/users/{id}', [AdminController::class, 'getUserDetails'])->name('users.details');
        Route::post('/users/{id}/disconnect', [AdminController::class, 'disconnectUser'])->name('users.disconnect');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.delete');
        
        // Configurações do Sistema
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        
        // Configurações de API
        Route::get('/api', [AdminController::class, 'apiSettings'])->name('api');
        Route::post('/api/gateway', [AdminController::class, 'updateGateway'])->name('api.update-gateway');
    });
});

