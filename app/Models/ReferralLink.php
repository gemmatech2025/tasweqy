<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $fillable = 
    [
        'brand_id',
        'link' ,
        'earning_precentage'
    ];

    public $timestamps = false;

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }   
}
