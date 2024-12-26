<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CardPassenger;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'amount',
        'passenger_ids',
    ];

    protected $casts = [
        'passenger_ids' => 'array',
    ];

    /**
     * Calculate the total payment based on selected passengers.
     */
    public static function calculateAmount(array $passengerIds)
    {
        // Fetch the selected passengers
        $totalAmount = CardPassenger::whereIn('id', $passengerIds)
            ->sum(DB::raw('cost + tax'));

        return $totalAmount;
    }
}
