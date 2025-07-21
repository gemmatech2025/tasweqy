<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandBlockImage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'brand_block_id',
        'image',
    ];

    public function brandBlock()
    {
        return $this->belongsTo(BrandBlock::class);
    }
}
