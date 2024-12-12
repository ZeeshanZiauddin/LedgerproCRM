<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardPassengerTable extends Migration
{
    public function up()
    {
        Schema::create('card_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('cards')->onDelete('cascade');  // Foreign key with cascading delete
            $table->string('name');
            $table->string('ticket_1')->nullable();  // Optional field
            $table->string('ticket_2')->nullable();  // Optional field
            $table->date('issue_date');
            $table->date('option_date')->nullable();  // Optional field
            $table->string('pnr')->nullable();  // Optional field
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_passengers');
    }
}

