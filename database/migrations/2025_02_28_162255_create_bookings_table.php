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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hall_id')->constrained('halls')->onDelete('cascade');
            $table->dateTime('event_date');
            $table->time('from');
            $table->time('to');
            $table->integer('guest_count');
            $table->string('event_type');
            $table->enum('status', ['unconfirmed', 'confirmed'])->default('unconfirmed');
            $table->boolean('payment_confirmed')->default(false);
            $table->text('additional_notes')->nullable();
            $table->text('condolence_additional_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
