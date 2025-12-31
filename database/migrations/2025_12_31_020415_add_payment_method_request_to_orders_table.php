<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah ke string dulu
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->change();
        });

        // 2. Drop constraint lama (nama constraint biasanya auto-generated)
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_method_check');

        // 3. Tambah constraint baru
        DB::statement("
        ALTER TABLE orders
        ADD CONSTRAINT orders_payment_method_check
        CHECK (payment_method IN ('cash', 'transfer', 'card', 'deposit'))
    ");

        // 4. Kolom baru
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('deposit_used', 10, 2)->default(0)->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kembalikan payment_method ke enum lama
            $table->enum('payment_method', ['cash', 'transfer', 'card'])
                ->default('cash')
                ->change();

            // Drop kolom deposit_used
            $table->dropColumn('deposit_used');
        });
    }
};
