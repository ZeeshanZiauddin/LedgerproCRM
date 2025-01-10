<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaidToPaymentPassengersTable extends Migration
{
    public function up()
    {
        Schema::table('payment_passengers', function (Blueprint $table) {
            $table->decimal('paid', 10, 2)->default(0.00)->after('card_passenger_id'); // Add a 'paid' column
        });
    }

    public function down()
    {
        Schema::table('payment_passengers', function (Blueprint $table) {
            $table->dropColumn('paid');
        });
    }
}
