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
        Schema::create('paymentways', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['e-pay' , 'cash','both'])->default('e-pay');
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymentways');
    }
};
