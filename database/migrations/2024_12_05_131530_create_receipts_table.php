<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->string('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->string('issued_by')->nullable();
            $table->string('modified_by')->nullable();
            $table->string('bank_no')->nullable();
            $table->string('dc_cc')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('changes', 15, 2)->nullable();
            $table->string('recon_acc')->nullable();
            $table->date('bank_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
