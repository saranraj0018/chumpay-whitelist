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
        Schema::create('shippings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->boolean('free_delivery')->default(false);
            $table->decimal('free_delivery_amount', 10, 2)->default(0.00);
            $table->decimal('minimum_delivery_amount', 10, 2)->default(0.00);
            $table->decimal('maximum_delivery_amount', 10, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->decimal('percent', 10, 2)->nullable();
            $table->decimal('default_shipping', 18, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('shipping_and_tax');
    }
};
