<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flight_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id'); // Foreign key to the card table (adjust according to your table)
            $table->string('airline');
            $table->string('flight');
            $table->string('class');
            $table->date('date');
            $table->string('from');
            $table->string('to');
            $table->time('dep');  // Departure time
            $table->time('arr');  // Arrival time
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade'); // Adjust the table name if needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flight_details');
    }
}
