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
            $table->id();
            $table->string('order_code', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('coffee_shop_id')->constrained('coffee_shops')->restrictOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('cart_version');
            $table->string('shop_name_snapshot', 120);
            $table->text('shop_address_snapshot');
            $table->string('customer_name_snapshot', 120);
            $table->string('customer_phone_snapshot', 30);
            $table->string('fulfillment_type', 20); // pickup, dine_in
            $table->string('table_label', 30)->nullable();
            $table->string('status', 30)->default('pending_payment'); // pending_payment, queued, processing, ready, completed, cancelled
            $table->string('payment_method', 30); // qris_simulation, cash
            $table->string('payment_status', 30)->default('pending'); // pending, paid, voided
            $table->unsignedBigInteger('total_amount');
            $table->string('customer_note', 500)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamp('stock_released_at')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'cart_version']);
            $table->index(['coffee_shop_id', 'status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']);
            $table->index(['coffee_shop_id', 'completed_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('menu_id')->constrained('menus')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('menu_name_snapshot', 120);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('menu_id');
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('event', 50);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->string('from_payment_status', 50)->nullable();
            $table->string('to_payment_status', 50)->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
