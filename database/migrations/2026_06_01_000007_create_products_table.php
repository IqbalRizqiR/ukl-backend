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
            $table->ulid('id')->primary();
            $table->foreignUlid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('brand', 100)->nullable();
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->text('description');
            $table->string('size', 10);
            $table->string('condition', 20);
            $table->string('color', 50)->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('weight_grams');
            $table->string('status', 20)->default('active');
            $table->integer('views_count')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('seller_id');
            $table->index('category_id');
            $table->index('status');
            $table->index(['status', 'created_at']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
