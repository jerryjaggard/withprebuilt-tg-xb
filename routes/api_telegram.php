<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Passport\TelegramController;
use App\Http\Controllers\V2\Admin\TelegramConfigController;

/*
|--------------------------------------------------------------------------
| Telegram API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for Telegram authentication and configuration
|
*/

// Public Telegram Auth Routes
Route::prefix('v1/passport/telegram')->group(function () {
    Route::get('/config', [TelegramController::class, 'config']);
    Route::post('/callback', [TelegramController::class, 'callback']);
});

// Authenticated User Telegram Routes
Route::prefix('v1/user/telegram')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/link', [TelegramController::class, 'link']);
    Route::delete('/unlink', [TelegramController::class, 'unlink']);
});

// Admin Telegram Configuration Routes
Route::prefix('v2/admin/telegram')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/config', [TelegramConfigController::class, 'index']);
    Route::put('/config', [TelegramConfigController::class, 'update']);
    Route::post('/test-bot', [TelegramConfigController::class, 'testBot']);
    Route::get('/stats', [TelegramConfigController::class, 'stats']);
    Route::post('/webhook/setup', [TelegramConfigController::class, 'setupWebhook']);
    Route::get('/webhook/info', [TelegramConfigController::class, 'webhookInfo']);
    Route::delete('/webhook', [TelegramConfigController::class, 'deleteWebhook']);
});