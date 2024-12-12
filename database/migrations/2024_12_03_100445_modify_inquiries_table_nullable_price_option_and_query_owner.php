<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Make 'price_option' and 'query_owner' nullable
            $table->string('price_option')->nullable()->change();
            $table->string('query_owner')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // Rollback changes by making them non-nullable
            $table->string('price_option')->nullable(false)->change();
            $table->string('query_owner')->nullable(false)->change();
        });
    }
};
