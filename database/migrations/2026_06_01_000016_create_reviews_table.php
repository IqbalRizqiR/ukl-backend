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
        Schema::create('reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('seller_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->restrictOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->text('seller_reply')->nullable();
            $table->timestamp('seller_replied_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
