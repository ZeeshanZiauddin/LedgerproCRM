<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleCostTaxMarginToCardPassengersTable extends Migration
{
    public function up()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            $table->decimal('sale', 10, 2)->nullable()->after('pnr');  // Sale price
            $table->decimal('cost', 10, 2)->nullable()->after('sale'); // Cost price
            $table->decimal('tax', 10, 2)->nullable()->after('cost');  // Tax amount
            $table->decimal('margin', 10, 2)->nullable()->after('tax'); // Profit margin
        });
    }

    public function down()
    {
        Schema::table('card_passengers', function (Blueprint $table) {
            $table->dropColumn(['sale', 'cost', 'tax', 'margin']);
        });
    }
}
