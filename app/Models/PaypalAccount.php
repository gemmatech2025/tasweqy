<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalAccount extends Model
{
    use HasFactory;

    protected $table = 'paypal_accounts';

    protected $fillable = [
        'email',
        'is_default',
        'user_id',
    ];

    /**
     * The user that owns the PayPal account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function withdrawRequests()
    {
        return $this->morphMany(WithdrawRequest::class, 'withdrawable');
    }
}
