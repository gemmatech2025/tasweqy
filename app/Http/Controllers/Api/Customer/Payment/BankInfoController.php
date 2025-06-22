<?php

namespace App\Http\Controllers\Api\Customer\Payment;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Models\BankInfo;
use App\Models\PaypalAccount;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Customer\Payment\BankInfoRequest;
use App\Http\Resources\Customer\Payment\BankInfoIndexResource;
use App\Http\Resources\Customer\Payment\BankInfoShowResource;

class BankInfoController extends BaseController
{


    protected const RESOURCE = BankInfoIndexResource::class;
    protected const RESOURCE_SHOW = BankInfoShowResource::class;
    protected const REQUEST = BankInfoRequest::class;

    public function model()
    {
        return   BankInfo::class; 
    }


    public function storeDefaultValues()
    {
        return ['user_id' => Auth::id()];
    }






public function index(Request $request)
{

  
    $bankInfo = BankInfo::where('user_id' , Auth::id())->get();
    $paypalAccounts = PaypalAccount::where('user_id' , Auth::id())->get();

    $data = [];
    foreach($bankInfo as $info){
        $data [] =['id' => $info->id,
            'account_name' => $info->account_name,
            'bank_name' => $info->bank_name,
            'is_default' => (bool) $info->is_default,

            'type' => 'bank_account'
        ];

    }
    foreach($paypalAccounts as $account){
        $data [] =['id' => $account->id,
            'email' => $account->email,
            'is_default' => (bool) $account->is_default,

            'type' => 'paypal_account'
        ];

    }


    return jsonResponse(
        true,
        200,
        __('messages.success'),
        $data
    );
}




public function setDefault($id , $type)
{
       DB::beginTransaction();
        try {

    if($type == 'paypal'){

        $account =   PaypalAccount::find($id);

        if(!$account){
            return jsonResponse(false, 404, __('messages.not_found'));
        }


        PaypalAccount::where('user_id', Auth::id())
        ->where('is_default', 1)
        ->update(['is_default' => 0]);

          BankInfo::where('user_id', Auth::id())
        ->where('is_default', 1)
        ->update(['is_default' => 0]);

        $account->is_default = '1';

        $account->save();
                    DB::commit();

    return jsonResponse(
        true,
        200,
        __('messages.success'),
    );

    }else if($type == 'bank'){

        $account =   BankInfo::find($id);

        if(!$account){
            return jsonResponse(false, 404, __('messages.not_found'));
        }


        PaypalAccount::where('user_id', Auth::id())
        ->where('is_default', 1)
        ->update(['is_default' => 0]);

        BankInfo::where('user_id', Auth::id())
        ->where('is_default', 1)
        ->update(['is_default' => 0]);

        $account->is_default = '1';

        $account->save();
            DB::commit();

    return jsonResponse(
        true,
        200,
        __('messages.success'),
    );


    }





    return jsonResponse(
        false,
        422,
        __('messages.wrong_type'),
    );
 }
        catch (\Throwable $e) {
            DB::rollBack();
            return jsonResponse(false, 500, __('messages.general_message'), null, null, [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }

}



}