<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_name');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who created the inquiry
            $table->date('date'); // Inquiry creation date
            $table->year('year'); // Year of the inquiry
            $table->string('owner_firstname');
            $table->string('owner_lastname');
            $table->string('status'); // Inquiry status (e.g., pending, confirmed)
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_mobile');
            $table->string('contact_home_number')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('price_option'); // e.g., fixed or negotiable
            $table->string('query_owner');
            $table->date('option_date')->nullable(); // Optional offer date
            $table->string('card_no')->nullable(); // Optional card number
            $table->string('pnr')->nullable(); // PNR if available
            $table->string('filter_point')->nullable(); // Custom filter point
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inquiries');
    }
};
