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

        Schema::create('wallet_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('min_amount', 12, 2);
            $table->decimal('max_amount', 12, 2);
            $table->decimal('bonus_amount', 12, 2)->nullable();
            $table->decimal('item_amount', 12, 2);
            $table->integer('valid_days')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->index(['min_amount', 'max_amount']);
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('bonus_balance', 12, 2)->default(0);
            $table->date('bonus_valid_until')->nullable();
            $table->foreignId('wallet_offer_id')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('wallet_offer_id')->references('id')->on('wallet_offers')->onDelete('no action');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('wallet_offer_id')->nullable();
            $table->enum('type', ['credit', 'debit', 'bonus']);
            $table->decimal('amount', 12, 2);
            $table->decimal('bonus_amount', 12, 2)->default(0.00);
            $table->date('bonus_valid_until')->nullable();
            $table->string('description')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('order_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->foreign('wallet_offer_id')->references('id')->on('wallet_offers')->onDelete('no action');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
    */
    public function down(): void
    {
        // Schema::dropIfExists('wallet');
    }
};
