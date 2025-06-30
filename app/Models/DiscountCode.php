<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    protected $fillable = 
    [
        'brand_id',
        'code' ,
        'earning_precentage'
    ];
    public $timestamps = false;


    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }   


    public function referralEarning()
    {
        return $this->morphOne(ReferralEarning::class, 'referrable');
    }


    public function isReserved(): bool
    {
        return $this->referralEarning()->exists();
    }
    // $reservedCodes = DiscountCode::whereHas('referralEarning')->get();
    // $notReservedCodes = DiscountCode::doesntHave('referralEarning')->get();



}
