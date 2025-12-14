<?php
// database/migrations/2024_01_01_000005_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            // Untuk tracking lengkap
            $table->integer('quantity')->default(1); // Jumlah baju/item
            $table->decimal('weight', 8, 2)->nullable(); // Berat dalam kg (untuk kiloan)

            // Pricing
            $table->decimal('unit_price', 10, 2); // Harga per unit (kg atau piece)
            $table->decimal('subtotal', 10, 2); // Total untuk item ini

            $table->text('notes')->nullable(); // Catatan per item
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
