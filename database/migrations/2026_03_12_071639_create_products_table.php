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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('name');
            $table->string('product_code')->unique();
            $table->text('description')->nullable();
            $table->string('main_image')->nullable();
            $table->enum('product_type', ['single', 'variant', 'bulk'])->default('single');
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->decimal('per_piece_price', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('no action');
            $table->foreign('sub_category_id')->references('id')->on('categories')->onDelete('no action');
        });

        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('value');
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);

            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->onDelete('no action');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('pri_attribute_id');
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('cover_image')->nullable();

            $table->timestamps();

            $table->foreign('pri_attribute_id')->references('id')->on('variant_attributes')->onDelete('no action');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('no action');
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');
            $table->timestamps();

            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('no action');
            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->onDelete('no action');
            $table->foreign('attribute_value_id')->references('id')->on('variant_attribute_values')->onDelete('no action');
        });

        Schema::create('bulk_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('minimum')->nullable();
            $table->integer('maximum')->nullable();
            $table->decimal('regular_price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();

            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('no action');
        });

        Schema::create('bulk_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id');

            $table->timestamps();

            $table->foreign('bulk_product_id')->references('id')->on('bulk_products')->onDelete('no action');
            $table->foreign('attribute_id')->references('id')->on('variant_attributes')->onDelete('no action');
            $table->foreign('attribute_value_id')->references('id')->on('variant_attribute_values')->onDelete('no action');
        });

        Schema::create('product_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('image_path');
            $table->timestamps();

            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('no action');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('products');
    }
};
