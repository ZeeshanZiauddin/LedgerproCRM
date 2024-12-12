<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Check if the 'settings' table already exists before creating it
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table): void {
                $table->id();

                $table->string('group');
                $table->string('name');
                $table->boolean('locked')->default(false);
                $table->json('payload');

                $table->timestamps();

                // Ensure group and name combination is unique
                $table->unique(['group', 'name']);
            });
        }
    }

    public function down(): void
    {
        // Drop the 'settings' table if it exists
        Schema::dropIfExists('settings');
    }
};
