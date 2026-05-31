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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('bank_account_id')->constrained('user_bank_accounts')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->string('reference_number', 100)->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
