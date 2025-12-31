<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_confirmation', ['now', 'later'])->default('later')->after('payment_method');
            $table->string('payment_proof')->nullable()->after('payment_confirmation');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_confirmation', 'payment_proof']);
        });
    }
};
