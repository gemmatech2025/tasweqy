<?php

namespace App\Http\Controllers\Api\Customer\Wallet;

use App\Http\Controllers\Controller;


use Illuminate\Support\Facades\DB;



use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Controllers\BasController\BaseController;
use App\Models\WithdrawRequest;
use App\Models\PaypalAccount;


use App\Http\Resources\Customer\Wallet\WithdrawRequestResource;
use App\Http\Requests\Customer\Wallet\WithdrawRequestRequest;
use App\Http\Requests\Customer\Wallet\UpdateWithdrawRequestRequest;


class WithdrawRequestController extends BaseController
{


    protected const RESOURCE = WithdrawRequestResource::class;
    protected const RESOURCE_SHOW = WithdrawRequestResource::class;
    protected const REQUEST = WithdrawRequestRequest::class;

    public function model()
    {
        return   WithdrawRequest::class; 
    }


    public function storeDefaultValues()
    {
        return ['user_id' => Auth::id()];
    }



    public function store(Request $request)
    {
        $reqClass      = static::REQUEST;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        DB::beginTransaction();

        try {
            $model = null;
            if($request->type == 'bank'){
                $bank = null;
                if($request->bank_account_id){
                    $bank =BankInfo::find($request->bank_account_id);
                }
                $bank =BankInfo::create([
                    'iban'                  => $request->iban,
                    'account_number'        => $request->account_number,
                    'account_name'          => $request->account_name,
                    'bank_name'             => $request->bank_name,
                    'swift_code'            => $request->swift_code,
                    'address'               => $request->address,
                    'user_id'               => Auth::id(),
                ]);
                $model = $paypal->withdrawRequests()->create([
                'user_id' => Auth::id(),
                'total'   => $request->total,
                ]);

            }else if($request->type == 'paypal'){
                $paypal = null;
                if($request->paypal_account_id){
                    $paypal =PaypalAccount::find($request->paypal_account_id);
                }
                $paypal =PaypalAccount::create([
                    'email'                 => $request->email,
                    'user_id'               => Auth::id(),

                ]);
                // $model = $paypal->withdrawRequests->create(['user_id'=> Auth::id(), 'total'=> $request->total]);
                $model = $paypal->withdrawRequests()->create([
                'user_id' => Auth::id(),
                'total'   => $request->total,
                ]);

            }

            




            DB::commit();

            return jsonResponse(
                true, 201, __('messages.add_success'),
                new (static::RESOURCE)($model)
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





    public function update(int $id, Request $request)
    {
        $reqClass      = UpdateWithdrawRequestRequest::class;
        $effectiveRequest = $reqClass !== Request::class
            ? app($reqClass)
            : $request;

        $validated = method_exists($effectiveRequest, 'validated')
            ? $effectiveRequest->validated()
            : $effectiveRequest->all();

        $excludeKeys = $this->uploadImages();
        $baseData    = array_diff_key($validated, array_flip($excludeKeys));
        DB::beginTransaction();
        try {
            $model = $this->getModel()->find($id);
            if (! $model) {
                return jsonResponse(false, 404, __('messages.not_found'));
            }

            if($model->status == 'approved'){
                return jsonResponse(false, 404, __('messages.can_not_edit_or_update_approved_request'));
            }
         
            if ($request->type == 'bank') {
                if ($request->bank_account_id) {
                    $bank = BankInfo::find($request->bank_account_id);
                    if ($bank) {
                        $model->withdrawable_id = $bank->id;
                        $model->withdrawable_type = BankInfo::class;
                    }
                }
            } else if ($request->type == 'paypal') {
                if ($request->paypal_account_id) {
                    $paypal = PaypalAccount::find($request->paypal_account_id);
                    if ($paypal) {
                        $model->withdrawable_id = $paypal->id;
                        $model->withdrawable_type = PaypalAccount::class;
                    }
                }
            }



            $model->total = $request->total;
            $model->save();
            
            DB::commit();
            return jsonResponse(
                true, 200, __('messages.update_success'),
                new (static::RESOURCE)($model)
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

    



public function delete(int $id)
{
    $model = $this->getModel()->find($id);

    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }

    
    $model->delete();

    return jsonResponse(true, 200, __('messages.delete_success'));
}


}