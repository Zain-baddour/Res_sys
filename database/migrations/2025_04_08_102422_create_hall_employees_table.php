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
        Schema::create('hall_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hall_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('hall_id')->references('id')->on('halls')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['hall_id', 'user_id']); // لمنع التكرار
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_employees');
    }
};
