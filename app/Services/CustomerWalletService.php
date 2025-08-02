<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTransaction;
use App\Models\Customer;
use App\Models\WithdrawRequest;

use Illuminate\Support\Str;


class CustomerWalletService
{


    // protected $fillable = ['code', 'amount', 'status', 'type', 'user_id'];

    public function withdrawFromCustomer($amount  , Customer $customer, $withdraw_request_id)
    {
        $code = random_int(100000, 999999);
        WalletTransaction::create(
            [
                'transatable_type'  => WithdrawRequest::class,
                'transatable_id'    => $withdraw_request_id,
                'code'              => $code,
                'amount'            => $amount,
                'status'            => 'approved',
                'type'              => 'withdraw',
                'user_id'           => $customer->user_id
            ]
        );


        $customer->total_balance -= $amount;
        $customer->save();

    }


}