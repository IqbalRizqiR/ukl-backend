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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->integer('province_id');
            $table->integer('city_id');
            $table->string('district', 100);
            $table->string('postal_code', 10);
            $table->text('full_address');
            $table->boolean('is_default')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('provinces');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
