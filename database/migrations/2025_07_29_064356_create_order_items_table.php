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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('qty_collected')->nullable();
            $table->unsignedInteger('qty_delivered')->nullable();
            $table->decimal('price', 10, 3)->default(0);
            $table->text('comment')->nullable();
            $table->text('delivery_comment')->nullable();

            $table->timestamps();

            // запрет на дублирование товара в одном заказе
            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
