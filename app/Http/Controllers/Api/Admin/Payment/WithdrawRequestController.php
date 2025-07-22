<?php

namespace App\Http\Controllers\Api\Admin\Payment;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\DiscountCode;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\FirebaseService;

use App\Http\Resources\Admin\Payment\WithdrawRequestResource;

class WithdrawRequestController extends Controller
{

    protected $firebaseService = null;
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }



public function updateRequestStatus($request_id , $status)
{
   DB::beginTransaction();
    try {

    $model = WithdrawRequest::find($request_id);
    if (!$model) {
        return jsonResponse(false, 404, __('messages.not_found'));
    }

    $customer = $model->user->customer;
    if($status == 'approved'){
    if($model->status != 'approved'){

        if(!$customer){
            return jsonResponse(false, 400, __('messages.user_dose_not_have_enough_earnings'));
        }

        if($customer->total_balance < $model->total){
            return jsonResponse(false, 400, __('messages.user_dose_not_have_enough_earnings'));
        }
        $this->firebaseService->handelNotification($model->user, 'withraw_success' , $model->id );
        $this->customerWalletService->withdrawFromCustomer($model->total ,$customer);
    }
    }else if ($status == 'rejected'){
        if($status != 'approved' && $model->status == 'approved'){
            return jsonResponse(false, 400, __('messages.cannot_update_approved_request'));
        }

        $this->firebaseService->handelNotification($model->user, 'withraw_issue' , $model->id );
    }else if ($status == 'pending'){
        if($status != 'approved' && $model->status == 'approved'){
            return jsonResponse(false, 400, __('messages.cannot_update_approved_request'));
        }
    }else{
        return jsonResponse(false, 400, __('messages.invalid_status'));
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

    public function getAllRequests(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $query = WithdrawRequest::query();
        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            WithdrawRequestResource::collection($data)
            ,
            $pagination
        );
    }



    public function show($id)
    {
        $request = WithdrawRequest::find($id);

        if (!$request) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }
        return jsonResponse(
            true,
            200,
            __('messages.success'),
            new WithdrawRequestResource($request)
        );


    }



}