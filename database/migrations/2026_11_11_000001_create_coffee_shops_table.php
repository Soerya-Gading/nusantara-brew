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
        Schema::create('coffee_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('opening_hours_text', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('status', 20)->default('pending'); // pending, active, inactive
            $table->boolean('is_accepting_orders')->default(false);
            $table->timestamps();

            $table->index('owner_user_id');
            $table->index(['status', 'is_accepting_orders']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffee_shops');
    }
};
