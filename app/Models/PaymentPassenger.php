<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPassenger extends Model
{
    use HasFactory;

    protected $fillable = ['payment_id', 'card_passenger_id', 'paid'];

    protected $casts = [
        'paid' => 'decimal:2',
    ];

}
