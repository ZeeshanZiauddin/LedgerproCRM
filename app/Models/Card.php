<?php
namespace App\Models;

use App\Filament\Resources\CardResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Card extends Model
{

    use HasFactory, LogsActivity, HasFilamentComments;


    protected $fillable = [
        'card_name',
        'user_id',
        'airline_id',
        'customer',
        'supplier',
        'inquiry_id',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'contact_home_number',
        'contact_other_number',
        'contact_address',
        'sales_price',
        'net_cost',
        'tax',
        'margin'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set `card_name` and `user_id` when creating a new card
        static::creating(function ($card) {
            // Generate a new card_name
            $latestInquiry = Card::latest('id')->first(); // Get the latest card
            $latestNumber = $latestInquiry ? (int) substr($latestInquiry->card_name, 2) : 0; // Extract the number part and increment it
            $newNumber = str_pad($latestNumber + 1, 7, '0', STR_PAD_LEFT); // Increment and pad the number with leading zeros

            $card->card_name = 'QT' . $newNumber; // Prefix with "QT"

            // Set the user_id to the currently logged-in user
            $card->user_id = auth()->id();
        });
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'text']);
    }

    public function passengers()
    {
        return $this->hasMany(CardPassenger::class);
    }
    public function flights()
    {
        return $this->hasMany(FlightDetails::class);
    }
    // relation with receipts
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'card_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class); // Defines an optional relationship to Inquiry
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class); // Defines an optional relationship to Inquiry
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class); // Defines an optional relationship to Inquiry
    }
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }




    /**
     * Get all receipts for this card and calculate total.
     *
     * @return array
     */
    public function getReceiptsStatus($quest = null)
    {
        // Get all receipts for this card
        $receipts = $this->receipts;

        // Calculate the total of all receipts
        $total = $receipts->sum('total');

        // Compare the total with sales_price
        $status = $this->getStatusColor(total: $total);

        if ($quest == 'status') {
            return $status;
        } elseif ($quest == 'total') {
            return $total;
        } else {
            return [
                'total' => $total,
                'status' => $status,
            ];
        }
    }



    /**
     * Get the status color based on total comparison with sales_price.
     *
     * @param float $total
     * @return string
     */
    protected function getStatusColor($total): array
    {

        if ($total < $this->sales_price) {
            return [
                'lable' => 'Under Paid',
                'color' => 'danger',
            ];
        } elseif ($total > $this->sales_price) {
            return [
                'lable' => 'Over Paid',
                'color' => 'warning',
            ];
        } else {
            return [
                'lable' => 'Completed',
                'color' => 'success',
            ];
        }
    }
}
