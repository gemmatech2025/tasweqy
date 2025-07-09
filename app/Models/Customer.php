<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'user_id',
        'birthdate',
        'gender',
        'total_balance',
        'is_verified',
        'is_blocked'
    ];


     protected $casts = [
        'is_blocked' => 'boolean',
        'is_verified' => 'boolean',
        'birthdate'   => 'date',
        'total_balance' => 'decimal:2',
    ];


    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
}
