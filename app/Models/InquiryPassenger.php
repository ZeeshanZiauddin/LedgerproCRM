<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'departure_id',
        'destination_id',
        'dep_date',
        'return_date',
        'adults',
        'child',
        'infants',
        'flight_type',
        'airline',
    ];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    // In InquiryPassenger.php
    public function departure()
    {
        return $this->belongsTo(Destination::class, 'departure_id');
    }


    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

}
