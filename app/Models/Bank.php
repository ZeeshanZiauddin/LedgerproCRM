<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starting',
        'limit',
        'user_id',
    ];

    // Define the relationship with User (if needed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

}
