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
        Schema::create('ttns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->date('date');
            $table->foreignId('courier_id')->nullable()->constrained('users'); // курьер
            //$table->foreignId('truck_id')->nullable()->constrained('trucks'); // машина
            $table->enum('status', ['new', 'in_progress', 'completed', 'cancelled'])->default('new');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ttns');
    }
};
