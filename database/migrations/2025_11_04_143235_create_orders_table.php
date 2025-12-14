<?php
// database/migrations/2024_01_01_000004_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('order_number')->unique();
            $table->timestamp('order_date');

            // Status
            $table->enum('status', ['request', 'process', 'washing', 'drying', 'ironing', 'packing', 'ready', 'completed', 'cancelled'])
                ->default('request');

            // Payment
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'transfer', 'deposit', 'qris'])->default('cash');

            // Amounts
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);

            // Tracking
            $table->decimal('weight', 8, 2)->nullable(); // Total berat (kg)
            $table->integer('total_items')->default(0); // Total jumlah baju/item

            // Dates
            $table->timestamp('estimated_completion')->nullable();
            $table->timestamp('actual_completion')->nullable();
            $table->timestamp('pickup_date')->nullable();

            // Notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            // WhatsApp tracking
            $table->boolean('whatsapp_sent')->default(false);
            $table->boolean('ready_notification_sent')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
