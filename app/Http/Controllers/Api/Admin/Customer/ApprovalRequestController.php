<?php

namespace App\Http\Controllers\Api\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\AccountVerificationRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\Customer\AccountVerificationRequestResource;
use App\Http\Resources\Admin\Customer\VerificationRequestResource;


use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;




use App\Http\Requests\Admin\Customer\UpdateAccountApprovalRequest;


class ApprovalRequestController extends Controller
{


    protected $searchService = null;
    public function __construct()
    {
        $this->searchService = new SearchService();
    }

    public function getRequests(Request $request)
    {


    $result =   $this->searchService->search(
        $request, AccountVerificationRequest::class, 
        AccountVerificationRequestResource::class,
        true,
        ['name'], 
        [] 
        );
    return $result;

    }

    public function show($id)
    {
        $request = AccountVerificationRequest::with(['approver'])->find($id); 
        if(!$request){
        return jsonResponse(false, 404, __('messages.not_found'));
        }      

        return jsonResponse(true, 200, __('messages.success' ), new AccountVerificationRequestResource($request));

    }



    public function delete($id)
    {
        $request = AccountVerificationRequest::with(['user' , 'approver'])->find($id); 
        if(!$request){
        return jsonResponse(false, 404, __('messages.not_found'));
        }   
        
        $request->delete();

        return jsonResponse(true, 200, __('messages.deleted_successfully' ));

    }
    



    public function updateApproval(UpdateAccountApprovalRequest $request ,$id)
    {
        $approvalRequest = AccountVerificationRequest::find($id); 
        if(!$approvalRequest){
        return jsonResponse(false, 404, __('messages.not_found'));
        }  
        if($request->new_status == 'rejected'){
            $approvalRequest->status = 'rejected';
            $approvalRequest->reason = $request->reason;
            $approvalRequest->approved_by = Auth::id();

            $customer =$approvalRequest->user->customer;
            if($customer){
                $customer->is_verified = '0';
                $customer->save();
            }
            $approvalRequest->save();


        }else if($request->new_status == 'approved' ){
            $approvalRequest->status = $request->new_status;
            $approvalRequest->approved_by = Auth::id();
            $approvalRequest->reason = null; // Clear reason if approved

            $customer =$approvalRequest->user->customer;
            if($customer){
                $customer->is_verified = '1';
                $customer->save();
            }
            $approvalRequest->save();
        }

        
        return jsonResponse(true, 200, __('messages.updates_successfully' ));

    }




        public function getApprovalRequestsByCustomerId(Request $request ,$customer_id)
        {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);




            $customer = Customer::find($customer_id); 
            if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        
        }
            $requests = AccountVerificationRequest::where('user_id', $customer->user_id); 
            $data = $requests->paginate($perPage, ['*'], 'page', $page);

            $pagination = [
                    'total' => $data->total(),
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'last_page' => $data->lastPage(),
                ];

        
            return jsonResponse(true, 200, __('messages.success') , VerificationRequestResource::collection($data), $pagination);

    }



    
}