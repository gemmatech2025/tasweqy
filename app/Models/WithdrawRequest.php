<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WithdrawRequest extends Model
{
    protected $fillable = ['user_id', 'total', 'status', 'withdrawable_type', 'withdrawable_id' , 'code'];

    protected $casts = [
        'total' => 'decimal:2',
        'status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawable()
    {
        return $this->morphTo();
    }



    public function walletTransaction()
    {
        return $this->morphOne(TrackingEvent::class, 'transatable');
    }

}
