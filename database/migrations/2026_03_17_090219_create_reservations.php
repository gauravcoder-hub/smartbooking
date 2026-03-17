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
   Schema::create('reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('table_id')->constrained()->cascadeOnDelete();
    $table->string('customer_name');
    $table->string('customer_email');
    $table->integer('guest_count');
    $table->date('reservation_date');
    $table->string('time_slot');
    $table->enum('status', ['booked', 'cancelled'])->default('booked');
    $table->text('special_request')->nullable();
    $table->timestamps();

    $table->unique(['table_id', 'reservation_date', 'time_slot']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
