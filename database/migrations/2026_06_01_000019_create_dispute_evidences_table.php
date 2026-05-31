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
        Schema::create('dispute_evidences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('dispute_id')->constrained('disputes')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 10);
            $table->string('image_url', 500)->nullable();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_evidences');
    }
};
