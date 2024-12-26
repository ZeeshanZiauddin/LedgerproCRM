<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'address',
        'fax',
        'comment',
        'credit_limit',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'text']);
    }

    /**
     * Boot method to handle auto-incrementing 'code'.
     */
    public static function boot()
    {
        parent::boot();

        // Hook into the creating event
        static::creating(function ($customer) {
            if (!$customer->code) {
                $customer->code = self::generateCode($customer->name);
            }
        });
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);

    }

    /**
     * Generate a unique code based on the customer's name.
     *
     * @param string $name
     * @return string
     */
    private static function generateCode(string $name): string
    {
        $initial = strtoupper(substr($name, 0, 1)); // Get the first letter of the name
        $latestCode = self::where('code', 'LIKE', "{$initial}%")
            ->orderBy('code', 'desc')
            ->value('code');

        $number = $latestCode ? (int) substr($latestCode, 1) + 1 : 1;

        return $initial . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
