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
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('counterparty_id')->constrained('counterparties');
            $table->foreignId('courier_id')->constrained('users');

            $table->decimal('paid_amount', 15, 3)->default(0);
            $table->decimal('debt_amount', 15, 3)->default(0);
            $table->timestamp('paid_at')->useCurrent();
            $table->string('confirm_code')->nullable();
            $table->boolean('debt_confirmed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
