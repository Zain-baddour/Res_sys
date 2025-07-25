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
        Schema::create('detail_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('from');
            $table->string('to');
            $table->string('description');
            $table->string('car_type');
            $table->integer('num_car');
            $table->date('date_day');
            $table->time('time');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_service_id')->constrained('office_services')->cascadeOnDelete();
            $table->timestamps();

    });

}
    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_bookings');
    }
};
