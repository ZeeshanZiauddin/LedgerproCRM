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
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('country');
            $table->string('continent');
            $table->unsignedBigInteger('user_id')->nullable(); // Foreign key for users
            $table->timestamps(); // Includes created_at and updated_at

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            // Drop foreign key before dropping the column
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('destinations');
    }
};
