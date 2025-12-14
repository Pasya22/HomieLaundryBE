<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable()->unique();
            $table->text('address')->nullable();
            $table->enum('type', ['regular', 'member'])->default('regular');
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->date('member_since')->nullable();
            $table->date('member_expiry')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
