<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'creator_id',
        'brand_id',
        'type',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(BrandBlockImage::class);
    }
}
