<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAirlineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('airlines', function (Blueprint $table) {
            // Add the 'iata' column
            $table->string('iata', 3)->after('code')->nullable(); // Adjust length and nullability as needed

            // Remove the 'comment' column
            $table->dropColumn('comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('airlines', function (Blueprint $table) {
            // Remove the 'iata' column if rolling back
            $table->dropColumn('iata');

            // Re-add the 'comment' column if rolling back
            $table->text('comment')->nullable();
        });
    }
}
