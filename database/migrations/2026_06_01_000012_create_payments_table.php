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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->string('midtrans_order_id', 100)->unique();
            $table->string('payment_type', 30)->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->string('snap_token', 255)->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->jsonb('midtrans_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
