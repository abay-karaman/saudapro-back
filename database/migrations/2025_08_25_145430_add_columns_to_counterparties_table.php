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
        Schema::table('counterparties', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('representative_id')->nullable()
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counterparties', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['representative_id']);
            $table->dropColumn(['user_id', 'representative_id']);
        });
    }
};
