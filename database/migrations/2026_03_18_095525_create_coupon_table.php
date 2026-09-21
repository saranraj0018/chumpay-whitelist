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

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code')->unique();
            $table->enum('discount_type', ['1', '2'])->default('2');
            $table->decimal('discount_value', 10, 2);
            $table->text('description');
            $table->enum('apply_for', ['1', '2'])->default('1');
            $table->double('max_price')->default(0);
            $table->double('min_price')->default(0);
            $table->integer('order_count')->nullable();
            $table->boolean('status')->default(true);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon');
    }
};
