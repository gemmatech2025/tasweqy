<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankInfo extends Model
{
    use HasFactory;

    protected $table = 'bank_info';

    protected $fillable = [
        'iban',
        'account_number',
        'account_name',
        'bank_name',
        'swift_code',
        'address',
        'is_default',
        'user_id',
    ];

    /**
     * The user that owns the bank info.
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
