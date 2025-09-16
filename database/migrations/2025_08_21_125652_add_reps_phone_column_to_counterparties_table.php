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
            $table->string('reps_phone')->nullable()->after('phone');
            $table->string('uid')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counterparties', function (Blueprint $table) {
            $table->dropColumn('reps_phone');
            $table->dropColumn('uid');
        });
    }
};
