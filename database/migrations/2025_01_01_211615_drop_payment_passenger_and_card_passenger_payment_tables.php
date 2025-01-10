<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPaymentPassengerAndCardPassengerPaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the payment_passenger table if it exists
        Schema::dropIfExists('payment_passenger');

        // Drop the card_passenger_payment table if it exists
        Schema::dropIfExists('card_passenger_payment');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the payment_passenger table
        Schema::create('payment_passenger', function (Blueprint $table) {
            $table->id(); // Primary key for the table
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete(); // Foreign key to payments table
            $table->foreignId('card_passenger_id')->constrained('card_passengers')->cascadeOnDelete(); // Foreign key to card_passengers table
            $table->timestamps(); // Add created_at and updated_at columns
        });

        // Recreate the card_passenger_payment table
        Schema::create('card_passenger_payment', function (Blueprint $table) {
            $table->id(); // Primary key for the table
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete(); // Foreign key to payments table
            $table->foreignId('card_passenger_id')->constrained('card_passengers')->cascadeOnDelete(); // Foreign key to card_passengers table
            $table->timestamps(); // Add created_at and updated_at columns
        });
    }
}
