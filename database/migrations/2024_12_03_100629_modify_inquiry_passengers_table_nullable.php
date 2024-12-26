<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Make all columns nullable
            $table->foreignId('inquiry_id')->change();
            $table->unsignedBigInteger('from_city_id')->nullable()->change();
            $table->unsignedBigInteger('from_country_id')->nullable()->change();
            $table->unsignedBigInteger('des_city_id')->nullable()->change();
            $table->unsignedBigInteger('des_country_id')->nullable()->change();
            $table->date('dep_date')->nullable()->change();
            $table->date('return_date')->nullable()->change();
            $table->unsignedInteger('adults')->nullable()->change();
            $table->unsignedInteger('child')->nullable()->change();
            $table->unsignedInteger('infants')->nullable()->change();
            $table->string('flight_type')->nullable()->change();
            $table->string('airline')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('inquiry_passengers', function (Blueprint $table) {
            // Rollback changes by making the columns non-nullable
            $table->foreignId('inquiry_id')->nullable(false)->change();
            $table->unsignedBigInteger('from_city_id')->nullable(false)->change();
            $table->unsignedBigInteger('from_country_id')->nullable(false)->change();
            $table->unsignedBigInteger('des_city_id')->nullable(false)->change();
            $table->unsignedBigInteger('des_country_id')->nullable(false)->change();
            $table->date('dep_date')->nullable(false)->change();
            $table->date('return_date')->nullable(false)->change();
            $table->unsignedInteger('adults')->nullable(false)->change();
            $table->unsignedInteger('child')->nullable(false)->change();
            $table->unsignedInteger('infants')->nullable(false)->change();
            $table->string('flight_type')->nullable(false)->change();
            $table->string('airline')->nullable(false)->change();
        });
    }
};
