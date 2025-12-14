<?php
// database/migrations/2024_01_01_000002_create_services_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // REGULER, EXPRESS, EKL_EXPRESS, EKL_REGULER
            $table->string('category'); // PAKAIAN, SELIMUT, BED_COVER, JAS, TAS, SEPATU
            $table->string('size')->nullable(); // SMALL, MEDIUM, KING, SUPER_KING
            $table->string('duration'); // 4JAM, 6JAM, 12JAM, 1HARI, 2HARI, 3HARI

            // Pricing System
            $table->decimal('price', 10, 2); // Harga dasar
            $table->decimal('member_price', 10, 2)->nullable(); // Harga member

            // Weight-based atau Piece-based
            $table->boolean('is_weight_based')->default(false); // true = per kg, false = per piece
            $table->decimal('price_per_kg', 10, 2)->nullable(); // Harga per kg
            $table->decimal('member_price_per_kg', 10, 2)->nullable(); // Harga member per kg

            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};
