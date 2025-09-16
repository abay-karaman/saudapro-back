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
        Schema::create('ttn_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('ttn_id')->constrained('ttns');
            $table->foreignId('order_id')->constrained('orders');
            $table->enum('status', ['new', 'in_progress', 'done'])->default('new');
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttn_items');
    }
};
