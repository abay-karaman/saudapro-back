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
        Schema::create('counterparty_representative', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counterparty_id')->constrained('counterparties')->cascadeOnDelete();
            $table->foreignId('representative_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['counterparty_id', 'representative_id'], 'count_rep_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counterparty_representative');
    }
};
