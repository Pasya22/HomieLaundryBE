 <?php

     Route::prefix('api')->group(function () {
         require __DIR__ . '/api.php';
     });

     // SPA Fallback - semua route ke React
     Route::get('/{any}', function () {
         return File::get(public_path('index.html'));
     })->where('any', '.*');
     //  Route::get('/', Dashboard::class)->name('dashboard');
     //  Route::get('/customers', CustomerIndex::class)->name('customers.index');
     //  Route::get('/services', ServiceIndex::class)->name('services.index');

     //  Route::get('/orders', OrderIndex::class)->name('orders.index');
     //  Route::get('/orders/create', CreateOrder::class)->name('orders.create');
     //  Route::get('/orders/{order}', ShowOrder::class)->name('orders.show');

     //  Route::get('/process', ProcessIndex::class)->name('process.index');
     //  Route::get('/invoice/{order}', InvoicePrint::class)->name('invoice.print');

     // Add other routes as needed
     Route::get('/health', function () {
         return 'OK';
 });
