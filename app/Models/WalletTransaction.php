<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WalletTransaction extends Model
{
    protected $fillable = [
        'transatable_type',
        'transatable_id',
        'code', 
        'amount', 
        'status', 
        'type', 
        'user_id'
    ];

    protected $casts = [

        'amount' => 'decimal:2',
        'status' => 'string',
        'type' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    


    public function transatable(): MorphTo
    {
        return $this->morphTo();
    }
}
