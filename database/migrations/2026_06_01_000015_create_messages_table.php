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
        Schema::create('messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignUlid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('type', 10)->default('text');
            $table->string('image_url', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
