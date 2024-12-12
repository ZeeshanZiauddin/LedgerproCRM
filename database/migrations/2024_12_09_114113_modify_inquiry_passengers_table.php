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
    public function up(): void
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Remove the old columns
            $table->dropColumn(['from_city_id', 'des_city_id']);

            // Add the new columns
            $table->unsignedBigInteger('departure_id')->nullable()->after('inquiry_id');
            $table->unsignedBigInteger('destination_id')->nullable()->after('dep_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Add back the removed columns
            $table->unsignedBigInteger('from_city_id')->nullable();
            $table->unsignedBigInteger('des_city_id')->nullable();

            // Remove the new columns
            $table->dropColumn(['departure_id', 'destination_id']);
        });
    }
};
