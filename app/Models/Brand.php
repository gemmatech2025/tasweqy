<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'logo',
        'category_id',
        'is_active',
        // 'referral_link_earning',
        'total_marketers',
    ];


    protected $casts = [
        'is_active' => 'boolean',
    ];


    public $translatable = ['name', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'brand_countries');
    }

    public function referralLinks()
    {
        return $this->hasMany(ReferralLink::class);
    }

    public function discountCodes()
    {
        return $this->hasMany(DiscountCode::class);
    }


    public function referralRequests()
    {
        return $this->hasMany(ReferralRequest::class);
    }



    
}
