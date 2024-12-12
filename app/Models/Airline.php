<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'comment',
        'status'
    ];



    public function inquiryPassengers()
    {
        return $this->hasMany(InquiryPassenger::class, 'airline_id');
    }
    protected static function booted()
    {
        static::creating(function ($airline) {
            // Ensure the status defaults to 'active' if not set.
            if (!$airline->status) {
                $airline->status = 'active';
            }
        });
    }
}
