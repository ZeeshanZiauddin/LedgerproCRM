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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn([
                'date',
                'year',
                'owner_firstname',
                'owner_lastname',
                'query_owner',
                'card_no',
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
        Schema::table('inquiries', function (Blueprint $table) {
            $table->date('date')->nullable();
            $table->integer('year')->nullable();
            $table->string('owner_firstname')->nullable();
            $table->string('owner_lastname')->nullable();
            $table->string('query_owner')->nullable();
            $table->string('card_no')->nullable();
        });
    }
};
