<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('cheque_no')->nullable(); // Cheque number (optional)
            $table->string('bank')->nullable(); // Bank name (optional)
            $table->decimal('total', 15, 2); // Total payment amount
            $table->text('tickets')->nullable(); // Associated ticket (card passenger) IDs
            $table->text('details')->nullable(); // Additional payment details
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
