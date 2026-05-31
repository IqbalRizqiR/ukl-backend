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
        Schema::create('shipments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('courier', 20);
            $table->string('service', 20);
            $table->string('tracking_number', 50)->nullable();
            $table->integer('weight_grams');
            $table->integer('origin_city_id');
            $table->integer('destination_city_id');
            $table->decimal('shipping_cost', 10, 2);
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
