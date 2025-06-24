<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandCountry extends Model
{
    protected $fillable = ['brand_id', 'country_id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
