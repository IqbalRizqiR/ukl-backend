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
        Schema::create('escrow_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamp('auto_release_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('auto_release_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escrow_transactions');
    }
};
