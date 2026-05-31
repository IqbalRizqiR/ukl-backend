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
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('order_number', 20)->unique();
            $table->foreignUlid('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('seller_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUlid('shipping_address_id')->constrained('user_addresses')->restrictOnDelete();
            $table->decimal('product_price', 12, 2);
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('service_fee', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('status', 20)->default('pending_payment');
            $table->text('notes')->nullable();
            $table->foreignUlid('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('buyer_id');
            $table->index('seller_id');
            $table->index('product_id');
            $table->index('status');
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
