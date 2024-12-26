<?php

namespace App\Models;

use App\Filament\Resources\InquiryResource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inquiry extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'inquiry_name',
        'user_id',
        'status',
        'contact_name',
        'contact_email',
        'contact_mobile',
        'contact_home_number',
        'contact_address',
        'price_option',
        'option_date',
        'card_no',
        'pnr',
        'filter_point',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'text']);
    }

    public function passengers()
    {
        return $this->hasMany(InquiryPassenger::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class); // Defines a one-to-many relationship with Card
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($inquiry) {
            if (empty($inquiry->inquiry_name)) {
                $inquiry->inquiry_name = InquiryResource::generateInquiryName();
            }
            if (empty($inquiry->user_id)) {
                $inquiry->user_id = auth()->id();
            }
        });
    }
}
