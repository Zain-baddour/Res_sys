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
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('hall_image')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->string('location');
            $table->integer('capacity');
            $table->string('contact');
            $table->enum('type' , ['joys' , 'sorrows' , 'both']);
            $table->json('events')->nullable();
            $table->json('pay_methods')->nullable()->default(null);
            $table->enum('status' , ['pending' , 'approved' , 'rejected' , 'expired'])->default('pending');
            $table->enum('rate', [1 , 2 , 3 , 4 , 5])->nullable()->default(null);
            $table->timestamp('subscription_expires_at')->default('2025-01-01 00:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }


};
