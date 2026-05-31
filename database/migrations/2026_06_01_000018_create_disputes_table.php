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
        Schema::create('disputes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('complainant_id')->constrained('users')->restrictOnDelete();
            $table->foreignUlid('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 30);
            $table->text('description');
            $table->string('status', 20)->default('open');
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
