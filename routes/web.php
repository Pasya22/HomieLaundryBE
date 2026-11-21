<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Homie Laundry API',
        'status' => 'running',
    ]);
});

// Jika ada browser/request yang mengakses endpoint yang tidak terdaftar, kembalikan JSON 404 (bukan mencari index.html)
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint tidak ditemukan.',
    ], 404);
});
