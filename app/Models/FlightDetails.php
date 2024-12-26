<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'airline',
        'flight',
        'class',
        'date',
        'from',
        'to',
        'dep',
        'arr'
    ];

    // Define the relationship with the card (assuming 'Card' model exists)
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
