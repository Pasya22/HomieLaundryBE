<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

/*
    |--------------------------------------------------------------------------
    | DASHBOARD 🔒
    |--------------------------------------------------------------------------
    */
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('/data', [DashboardController::class, 'dashboardData']);
});

/*
    |--------------------------------------------------------------------------
    | ORDERS 🔒
    |--------------------------------------------------------------------------
    */
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/stats', [OrderController::class, 'stats']);
    Route::get('/search', [OrderController::class, 'searchCustomers']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/{id}/payment', [OrderController::class, 'updatePaymentStatus']);
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});

/*
    |--------------------------------------------------------------------------
    | CUSTOMERS 🔒
    |--------------------------------------------------------------------------
    */
Route::prefix('customers')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('/{id}', [CustomerController::class, 'show']);
    Route::put('/{id}', [CustomerController::class, 'update']);
    Route::delete('/{id}', [CustomerController::class, 'destroy']);
});

/*
    |--------------------------------------------------------------------------
    | SERVICES 🔒
    |--------------------------------------------------------------------------
    */
Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']); // grouped
    Route::post('/', [ServiceController::class, 'store']);
    Route::get('/{id}', [ServiceController::class, 'show']);
    Route::put('/{id}', [ServiceController::class, 'update']);
    Route::delete('/{id}', [ServiceController::class, 'destroy']);
});
/*
|--------------------------------------------------------------------------
| HEALTH (Public)
|--------------------------------------------------------------------------
*/
Route::get('/health', fn() => response()->json(['status' => 'ok']));
Route::get('/version', fn() => response()->json(['version' => '1.0.0']));

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES 🔒
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH 🔒
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    /*
    |--------------------------------------------------------------------------
    | PROCESS 🔒
    |--------------------------------------------------------------------------
    */
    Route::prefix('process')->group(function () {
        Route::get('/', [ProcessController::class, 'index']);
        Route::patch('/{id}/status', [ProcessController::class, 'updateStatus']);
        Route::patch('/{id}/quick-update', [ProcessController::class, 'quickUpdate']);
        Route::patch('/{id}/mark-ready', [ProcessController::class, 'markAsReady']);
        Route::patch('/{id}/mark-completed', [ProcessController::class, 'markAsCompleted']);
    });
});

/*
|--------------------------------------------------------------------------
| SPA FALLBACK
|--------------------------------------------------------------------------
*/
Route::get('/{any}', function () {
    return File::get(public_path('index.html'));
})->where('any', '.*');
