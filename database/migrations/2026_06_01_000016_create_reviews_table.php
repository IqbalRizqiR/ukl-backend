<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->text('seller_reply')->nullable();
            $table->timestamp('seller_replied_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['seller_id', 'created_at']);
        });

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT reviews_rating_check CHECK (rating BETWEEN 1 AND 5)');
        DB::statement('
    ALTER TABLE reviews ADD CONSTRAINT reviews_reply_consistency 
    CHECK (
        (seller_reply IS NULL AND seller_replied_at IS NULL) OR
        (seller_reply IS NOT NULL AND seller_replied_at IS NOT NULL)
    )
');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
