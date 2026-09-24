<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tanan tables sa POS database
return new class extends Migration
{
    public function up(): void
    {
        // I-set ang MySQL engine ug charset para production-ready
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Mga kategorya (Burger, Chicken, Drinks, etc.)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Mga produkto/menu items
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Mga mesa sa restaurant
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->integer('table_number')->unique();
            $table->integer('capacity')->default(4);
            $table->string('floor')->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamps();
        });

        // Mga order
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'preparing', 'served', 'cancelled'])->default('pending');
            $table->enum('order_type', ['dine-in', 'takeout'])->default('dine-in');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('amount_tendered', 10, 2)->default(0);
            $table->decimal('change', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Mga item sa sulod sa order
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // I-disable ang FK checks para limpyo ang drop
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Tangtangon sa labing bata paubos
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');

        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
