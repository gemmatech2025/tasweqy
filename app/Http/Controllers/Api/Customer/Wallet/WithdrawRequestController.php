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
use App\Models\BankInfo;


use App\Http\Resources\Customer\Wallet\WithdrawRequestResource;
use App\Http\Requests\Customer\Wallet\WithdrawRequestRequest;
use App\Http\Requests\Customer\Wallet\UpdateWithdrawRequestRequest;

use App\Services\CustomerWalletService;
use App\Services\FirebaseService;

class WithdrawRequestController extends BaseController
{


    protected const RESOURCE = WithdrawRequestResource::class;
    protected const RESOURCE_SHOW = WithdrawRequestResource::class;
    protected const REQUEST = WithdrawRequestRequest::class;

    
    protected $customerWalletService =null;
    protected $firebaseService =null;

    public function __construct()
    {
        $this->model = $this->model();
        $this->customerWalletService = new CustomerWalletService();
        $this->firebaseService = new FirebaseService();

    }

    

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
                }else{

                    $bank =BankInfo::create([
                    'iban'                  => $request->iban,
                    'account_number'        => $request->account_number,
                    'account_name'          => $request->account_name,
                    'bank_name'             => $request->bank_name,
                    'swift_code'            => $request->swift_code,
                    'address'               => $request->address,
                    'user_id'               => Auth::id(),
                ]);

                }             
                $model = $bank->withdrawRequests()->create([
                'user_id' => Auth::id(),
                'total'   => $request->total,
                ]);

            }else if($request->type == 'paypal'){
                $paypal = null;
                if($request->paypal_account_id){
                    $paypal =PaypalAccount::find($request->paypal_account_id);
                }else{

                $paypal =PaypalAccount::where('email' ,$request->email)
                ->where('user_id' ,Auth::id())->first();


                if(!$paypal){
                    $paypal = PaypalAccount::create([
                                    'email'                 => $request->email,
                                    'user_id'               => Auth::id(),
                                ]);
                    }
                }
                $code = $this->generateCode();
                
                $model = $paypal->withdrawRequests()->create([
                    'user_id' => Auth::id(),
                    'total'   => $request->total,
                    'code'    => $code,
                ]);

            }
            $user =Auth::user();
            $this->firebaseService->sendAdminNotification('withdraw_request_added', $model->id ,  $user);
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

    public function generateCode()
    {
        $code = random_int(100000, 999999); 
        while (WithdrawRequest::where('code', $code)->exists()) {
            $code = random_int(100000, 999999); 
        }
        return $code;

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







public function getMyRequests()
{
    $user = Auth::user();
    $withdrawRequests = WithdrawRequest::where('user_id' , $user->id)->get();
    return jsonResponse(
        true,
        200,
        __('messages.success'),
        (static::RESOURCE)::collection($withdrawRequests)
    );
}





public function updateRequestStatus($request_id , $status)
{
   DB::beginTransaction();
    try {

    $model = $this->getModel()->find($request_id);
    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }

    $customer = $model->user->customer;
    if($status == 'approved' && $model->status != 'approved'){
        if(!$customer){
            return jsonResponse(false, 400, __('messages.user_dose_not_have_enough_earnings'));
        }

        if($customer->total_balance < $model->total){
            return jsonResponse(false, 400, __('messages.user_dose_not_have_enough_earnings'));
        }
        $this->customerWalletService->withdrawFromCustomer($model->total ,$customer);
    }

    if($status != 'approved' && $model->status == 'approved'){
        return jsonResponse(false, 400, __('messages.cannot_update_approved_request'));
    }

    $model->status = $status;
    $model->save();
    DB::commit();

    return jsonResponse(
        true,
        200,
        __('messages.success'));

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