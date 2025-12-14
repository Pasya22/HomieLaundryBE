 <?php

     use App\Http\Controllers\Api\CustomerController;
     use App\Http\Controllers\Api\DashboardController;
     use App\Http\Controllers\Api\OrderController;
     use App\Http\Controllers\Api\ServiceController;

     // routes/api.php
     Route::prefix('/')->group(function () {
         Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
         Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
         Route::get('/dashboard/data', [DashboardController::class, 'dashboardData']);

         Route::prefix('orders')->group(function () {
             Route::get('/', [OrderController::class, 'index']);
             Route::get('/stats', [OrderController::class, 'stats']);
             Route::post('/', [OrderController::class, 'store']);
             Route::get('/{id}', [OrderController::class, 'show']);
             Route::patch('/{id}/status', [OrderController::class, 'updateStatus']);
             Route::patch('/{id}/payment', [OrderController::class, 'updatePaymentStatus']);
             Route::delete('/{id}', [OrderController::class, 'destroy']);
         });

         // Customers
         Route::prefix('customers')->group(function () {
             Route::get('/', [CustomerController::class, 'index']);
             Route::get('/search', [OrderController::class, 'searchCustomers']);
             Route::post('/', [CustomerController::class, 'store']);
             Route::get('/{id}', [CustomerController::class, 'show']);
             Route::put('/{id}', [CustomerController::class, 'update']);
             Route::delete('/{id}', [CustomerController::class, 'destroy']);
         });

         // Services
         Route::prefix('services')->group(function () {
             Route::get('/', [ServiceController::class, 'index']);
             Route::post('/', [ServiceController::class, 'store']);
             Route::put('/{id}', [ServiceController::class, 'update']);
             Route::delete('/{id}', [ServiceController::class, 'destroy']);
         });
     });
     Route::get('/{any}', function () {
         return File::get(public_path('index.html'));
 })->where('any', '.*');
