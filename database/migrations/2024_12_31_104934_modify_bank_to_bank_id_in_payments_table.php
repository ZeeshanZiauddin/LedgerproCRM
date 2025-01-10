<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the old 'bank' column if it exists
            $table->dropColumn('bank');

            // Add the new 'bank_id' column as a foreign key
            $table->unsignedBigInteger('bank_id')->after('supplier_id')->nullable();
            $table->text('passenger_ids')->after('bank_id')->nullable();
            // Add the foreign key constraint to 'bank_id'
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key and the 'bank_id' column if rolling back
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');

            // Recreate the old 'bank' column if rolling back
            $table->string('bank')->nullable();
        });
    }
};
