<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPassengerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_passenger', function (Blueprint $table) {
            $table->id(); // Primary key for the table
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete(); // Foreign key to payments table
            $table->foreignId('card_passenger_id')->constrained('card_passengers')->cascadeOnDelete(); // Foreign key to card_passengers table
            $table->timestamps(); // Add created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_passenger');
    }
}
