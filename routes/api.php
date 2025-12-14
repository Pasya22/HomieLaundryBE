 <?php

     use App\Http\Controllers\Api\CustomerController;
     use App\Http\Controllers\Api\DashboardController;

     // routes/api.php
     Route::prefix('/')->group(function () {
         Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
         Route::get('/dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
         Route::get('/dashboard/data', [DashboardController::class, 'dashboardData']);

         Route::apiResource('customers', CustomerController::class);
     });
     Route::get('/{any}', function () {
     return File::get(public_path('index.html'));
 })->where('any', '.*');
