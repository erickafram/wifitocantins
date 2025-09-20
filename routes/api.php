<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\MikrotikController;
use App\Http\Controllers\MikrotikSyncController;
use App\Http\Controllers\RegistrationController;

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

// Detectar dispositivo
Route::post('/detect-device', [PortalController::class, 'detectDevice']);

// Registro de usuários
Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/register-for-payment', [RegistrationController::class, 'registerForPayment']);
Route::post('/check-email', [RegistrationController::class, 'checkEmail']);
Route::post('/check-user', [RegistrationController::class, 'checkUser']);

// Pagamentos
Route::prefix('payment')->group(function () {
    Route::post('/pix', [PaymentController::class, 'processPix']);
    Route::post('/pix/generate-qr', [PaymentController::class, 'generatePixQRCode']);
    Route::get('/pix/status', [PaymentController::class, 'checkPixStatus']);
    Route::post('/card', [PaymentController::class, 'processCard']);
    Route::post('/process', [PaymentController::class, 'process']);
    Route::post('/webhook', [PaymentController::class, 'webhook']);
    Route::post('/webhook/santander', [PaymentController::class, 'santanderWebhook']);
    Route::post('/webhook/woovi', [PaymentController::class, 'wooviWebhook']);
    Route::post('/webhook/woovi/created', [PaymentController::class, 'wooviWebhookCreated']);
    Route::post('/webhook/woovi/expired', [PaymentController::class, 'wooviWebhookExpired']);
    Route::post('/webhook/woovi/transaction', [PaymentController::class, 'wooviWebhookTransaction']);
    Route::post('/webhook/woovi/different-payer', [PaymentController::class, 'wooviWebhookDifferentPayer']);
    
    // Webhook unificado com retry automático
    Route::post('/webhook/woovi/unified', [PaymentController::class, 'wooviWebhookUnified']);
    Route::get('/test-santander', [PaymentController::class, 'testSantanderConnection']);
    Route::get('/test-woovi', [PaymentController::class, 'testWooviConnection']);
});

// Vouchers
Route::prefix('voucher')->group(function () {
    Route::post('/apply', [VoucherController::class, 'apply']);
    Route::get('/validate/{code}', [VoucherController::class, 'validate']);
});

// Instagram Free Access
Route::post('/instagram/free-access', [PortalController::class, 'instagramFreeAccess']);

// WireGuard Sync (Secure Tunnel)
Route::prefix('mikrotik-sync')->group(function () {
    Route::post('/real-macs', [App\Http\Controllers\WireGuardSyncController::class, 'receiveRealMacs']);
    Route::post('/new-client', [App\Http\Controllers\WireGuardSyncController::class, 'newClient']);
    Route::post('/heartbeat', [App\Http\Controllers\WireGuardSyncController::class, 'heartbeat']);
});

// MikroTik Integration (Legacy - Direct API)
Route::prefix('mikrotik')->group(function () {
    Route::get('/status/{mac}', [MikrotikController::class, 'getStatus']);
    Route::post('/allow', [MikrotikController::class, 'allowDevice']);
    Route::post('/block', [MikrotikController::class, 'blockDevice']);
    Route::get('/usage/{mac}', [MikrotikController::class, 'getUsage']);
});

// MikroTik Sync (New - HTTP Polling)
Route::prefix('mikrotik-sync')->group(function () {
    Route::get('/ping', [MikrotikSyncController::class, 'ping']);
    Route::match(['GET', 'POST'], '/pending-users', [MikrotikSyncController::class, 'getPendingUsers']);
    Route::post('/check-access', [MikrotikSyncController::class, 'checkUserAccess']);
    Route::post('/report-status', [MikrotikSyncController::class, 'reportUserStatus']);
    Route::get('/stats', [MikrotikSyncController::class, 'getStats']);
});
