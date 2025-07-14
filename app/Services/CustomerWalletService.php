<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTransaction;
use App\Models\Customer;

use Illuminate\Support\Str;


class CustomerWalletService
{


    // protected $fillable = ['code', 'amount', 'status', 'type', 'user_id'];

    public function withdrawFromCustomer($amount  , Customer $customer)
    {
        $code = random_int(100000, 999999);
        WalletTransaction::create(
            [
                'code'       => $code,
                'amount'     => $amount,
                'status'     => 'approved',
                'type'       => 'withdraw',
                'user_id'    => $customer->user_id
            ]
        );


        $customer->total_balance -= $amount;
        $customer->save();

    }


}