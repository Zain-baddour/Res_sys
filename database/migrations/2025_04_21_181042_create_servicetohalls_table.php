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
        Schema::create('servicetohalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete('');
            $table->enum('name', ['buffet_service','hospitality_services','performance_service','car_service','decoration_service','photographer_service','protection_service',
            'promo_service','reader_service','condolence_photographer_service','condolence_hospitality_services']);
            $table->decimal('service_price', 10, 2);
            $table->json('description');
            $table->boolean('is_fixed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicetohalls');
    }
};
