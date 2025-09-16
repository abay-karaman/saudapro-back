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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('qty_collected')->nullable()->after('quantity');
            $table->unsignedInteger('qty_delivered')->nullable()->after('qty_collected');
            $table->text('delivery_comment')->nullable()->after('qty_delivered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['qty_collected', 'qty_delivered', 'delivery_comment']);
        });
    }
};
