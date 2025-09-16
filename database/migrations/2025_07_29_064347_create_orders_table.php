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
            $table->uuid('uid')->unique();
            $table->foreignId('representative_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counterparty_id')->constrained('counterparties');
            $table->foreignId('store_id')->constrained('stores');
            $table->decimal('total_price', 15, 3)->default(0);
            $table->decimal('total_collected', 15, 3)->default(0);
            $table->decimal('total_delivered', 15, 3)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'delivered', 'cancelled'])->default('pending');
            $table->text('comment')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'debt'])->nullable();
            $table->enum('source', ['app', '1C'])->nullable()->default('app');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
