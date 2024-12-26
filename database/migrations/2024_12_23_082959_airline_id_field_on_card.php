<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->unsignedBigInteger('airline_id')->nullable()->after('id'); // Add airline_id field
            $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('set null'); // Foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('card', function (Blueprint $table) {
            $table->dropForeign(['airline_id']); // Drop foreign key constraint
            $table->dropColumn('airline_id'); // Drop the airline_id field
        });
    }
};
