<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Receipt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'user_id',
        'card_id',
        'customer_id',
        'date',
        'year',
        'issued_by',
        'modified_by',
        'bank_no',
        'dc_cc',
        'total',
        'changes',
        'recon_acc',
        'bank_date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'text']);
    }

    /**
     * Relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    /**
     * Relationship with Card.
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    //Before creating set code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            $latestReceipt = static::lockForUpdate()->latest('id')->first(); // Use locking for concurrency
            $nextNumber = $latestReceipt ? intval(substr($latestReceipt->name, 2)) + 1 : 1;
            $receipt->name = 'SR' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
        });
    }

}
