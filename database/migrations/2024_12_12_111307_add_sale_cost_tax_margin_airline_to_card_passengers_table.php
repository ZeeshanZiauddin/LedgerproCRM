<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleCostTaxMarginAirlineToCardPassengersTable extends Migration
{
    public function up()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            // Adding new columns for sale, cost, tax, and margin
            // $table->decimal('sale', 10, 2)->nullable()->after('pnr');  // Sale price
            // $table->decimal('cost', 10, 2)->nullable()->after('sale'); // Cost price
            // $table->decimal('tax', 10, 2)->nullable()->after('cost');  // Tax amount
            // $table->decimal('margin', 10, 2)->nullable()->after('tax'); // Profit margin

            // Adding the foreign key column for airline_id
            $table->foreignId('airline_id')->nullable()->constrained('airlines')->onDelete('cascade')->after('margin');
        });
    }

    public function down()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            // Dropping the added columns including the foreign key
            // $table->dropColumn(['sale', 'cost', 'tax', 'margin']);
            $table->dropForeign(['airline_id']); // Drop the foreign key constraint
            $table->dropColumn('airline_id');    // Drop the airline_id column
        });
    }
}
