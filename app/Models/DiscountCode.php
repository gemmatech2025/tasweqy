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
    protected $timestamps = false;


    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }   
}
