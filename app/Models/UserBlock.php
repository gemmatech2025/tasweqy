<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'creator_id',
        'customer_id',
        'type',
    ];

    /**
     * The user who created the block.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * The user who is blocked.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Related block images.
     */
    public function images()
    {
        return $this->hasMany(UserBlockImage::class);
    }
}
