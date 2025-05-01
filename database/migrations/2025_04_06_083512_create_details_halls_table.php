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
        Schema::create('details_halls', function (Blueprint $table) {
            $table->id();
            $table->enum('type_hall',['wedding','sorrow','both']);
            $table->json('card_price')->nullable();
            $table->string('number');
            $table->string('location');
            $table->integer('num_person');
            $table->integer('res_price');
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details_halls');
    }
};
