<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $fillable = 
    [
        'link_code',
        'brand_id',
        'link' ,
        'earning_precentage'
    ];

    // public $timestamps = false;

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }   



    public function referralEarning()
    {
        return $this->morphOne(ReferralEarning::class, 'referrable');
    }


    public function trackingEvents()
    {
        return $this->morphMany(TrackingEvent::class, 'trackable');
    }



    public function isReserved(): bool
    {
        return $this->referralEarning()->exists();
    }


    // public function transatable()
    // {
    //     return $this->morphTo();
    // }
}
