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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->smallInteger('address_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->longText('other')->nullable();
            $table->string('type')->default('order');
            $table->string('transaction_id')->nullable();
            $table->boolean('is_buy_now')->default(0);
            $table->string('checkout_id')->nullable();
            $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('payments');
    }
};
