<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_name')->unique();  // Ensure card names are unique
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('customer_id')->nullable();
            $table->string('supplier_id')->nullable();
            $table->foreignId('inquiry_id')->nullable()->constrained('inquiries')->onDelete('set null');  // Foreign key to inquiries table
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();  // Email can be nullable and unique
            $table->string('contact_mobile')->nullable();  // Optional
            $table->string('contact_home_number')->nullable();  // Optional
            $table->string('contact_other_number')->nullable();  // Optional
            $table->text('contact_address')->nullable();  // Optional
            $table->decimal('sales_price', 10, 2)->default(0);  // Default value for sales_price
            $table->decimal('net_cost', 10, 2)->default(0);  // Default value for net_cost
            $table->decimal('tax', 10, 2)->default(0);  // Default value for tax
            $table->decimal('margin', 10, 2)->default(0);  // Default value for margin
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cards');
    }
}
