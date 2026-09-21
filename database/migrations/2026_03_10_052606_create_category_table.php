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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 - In Active , 1 - Active');
            $table->enum('gender', ['male', 'female', 'others'])->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('no action');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};
