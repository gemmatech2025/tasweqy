<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserBlockImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_block_id',
        'image',
    ];
    public $timestamps = false; 


    public function userBlock()
    {
        return $this->belongsTo(UserBlock::class);
    }
}
