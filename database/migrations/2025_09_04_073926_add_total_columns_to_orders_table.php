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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_collected', 15, 3)->default(0)->after('total_price');
            $table->decimal('total_delivered', 15, 3)->default(0)->after('total_collected');
            $table->enum('source', ['app', '1C'])->nullable()->default('app');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_collected', 'total_delivered', 'source']);
        });
    }
};
