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
        Schema::create('loungetimings', function (Blueprint $table) {
            $table->id();
            $table->enum('type' , ['evening' , 'morning'])->default('evening');
            $table->timestamp('from')->nullable();
            $table->timestamp('to')->nullable();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loungetimings');
    }
};
