<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('inquiry_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->onDelete('cascade'); // Link to inquiries table
            $table->unsignedBigInteger('from_city_id'); // Departure city
            $table->unsignedBigInteger('from_country_id'); // Departure country
            $table->unsignedBigInteger('des_city_id'); // Destination city
            $table->unsignedBigInteger('des_country_id'); // Destination country
            $table->date('dep_date')->nullable(); // Departure date
            $table->date('return_date')->nullable(); // Return date
            $table->unsignedInteger('adults')->default(0); // Number of adults
            $table->unsignedInteger('child')->default(0); // Number of children
            $table->unsignedInteger('infants')->default(0); // Number of infants
            $table->string('flight_type'); // e.g., Economy, Business
            $table->string('airline')->nullable(); // Preferred airline
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inquiry_passengers');
    }
};
