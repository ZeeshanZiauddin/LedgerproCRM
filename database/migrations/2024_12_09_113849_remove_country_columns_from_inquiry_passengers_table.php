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
            $table->dropColumn([
                'des_country_id',
                'from_country_id',
            ]);
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
            $table->unsignedBigInteger('des_country_id')->nullable();
            $table->unsignedBigInteger('from_country_id')->nullable();
        });
    }
};
