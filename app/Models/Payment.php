<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'cheque_no',
        'bank_id',
        'passenger_ids',
        'total',
        'name',
        'tickets',
        'details',
    ];

    protected $casts = [
        'passenger_ids' => 'array', // Cast to array to ensure proper conversion
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function cardPassengers()
    {
        return $this->belongsToMany(CardPassenger::class, 'payment_passengers', 'payment_id', 'card_passenger_id')
            ->withPivot('paid');
    }

    protected static function boot()
    {
        parent::boot();

        // Generate the name before saving the payment
        static::creating(function ($payment) {
            // dd($payment);
            $lastId = static::max('id') ?? 0; // Get the highest ID in the table
            $newId = $lastId + 1;
            $payment->name = 'SP' . str_pad($newId, 6, '0', STR_PAD_LEFT);
        });

    }

}
