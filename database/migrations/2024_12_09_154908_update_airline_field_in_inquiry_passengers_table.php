<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAirlineFieldInInquiryPassengersTable extends Migration
{
    public function up()
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Drop the 'airline' column
            $table->dropColumn('airline_id');

            // Add the new 'airline_id' column
            $table->unsignedBigInteger('airline_id')->nullable(); // Add new column as nullable initially

            // Add foreign key constraint
            $table->foreign('airline_id')
                ->references('id')
                ->on('airlines')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Drop the foreign key and the 'airline_id' column
            $table->dropForeign(['airline_id']);
            $table->dropColumn('airline_id');

            // Re-add the 'airline' column
            $table->string('airline_id')->nullable();
        });
    }
}
