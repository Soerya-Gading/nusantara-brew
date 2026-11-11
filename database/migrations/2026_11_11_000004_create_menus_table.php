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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coffee_shop_id')->constrained('coffee_shops')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price');
            $table->integer('stock')->default(0);
            $table->string('image_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            // Composite FK: ensure category belongs to same shop
            $table->foreign(['category_id', 'coffee_shop_id'])
                  ->references(['id', 'coffee_shop_id'])
                  ->on('categories')
                  ->restrictOnDelete()
                  ->cascadeOnUpdate();

            $table->index(['coffee_shop_id', 'is_active', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
