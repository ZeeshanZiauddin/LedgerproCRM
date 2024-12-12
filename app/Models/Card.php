<?php
namespace App\Models;

use App\Filament\Resources\CardResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Card extends Model
{

    use HasFactory, LogsActivity;


    protected $fillable = [
        'card_name',
        'user_id',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'text']);
    }

    public function passengers()
    {
        return $this->hasMany(CardPassenger::class);
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
