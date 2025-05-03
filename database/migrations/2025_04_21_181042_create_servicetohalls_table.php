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
            $table->enum('name', ['buffet','hospitality','performance','car','decoration','photographer','protection',
            'promo','reader','condolence_photographer','condolence_hospitality']);
            $table->integer('price');
            $table->string('description');
            $table->string('video_path');
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete('');
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
