<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('from');
            $table->string('to');
            $table->string('car_type');
            $table->integer('num_car');
            $table->date('date_day');
            $table->time('date');
           
           // $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_bookings');
    }
};
