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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_number', 255)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->enum('gender', ['m', 'f', 'o'])->comment('m -> Male, f -> Female , o -> others ')->nullable();
            $table->integer('age')->nullable();
            $table->string('referal_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
