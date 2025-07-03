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
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;




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
        $request = AccountVerificationRequest::with(['user' , 'approver'])->find($id); 
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
        $approvalRequest = AccountVerificationRequest::with(['user' , 'approver'])->find($id); 
        if(!$approvalRequest){
        return jsonResponse(false, 404, __('messages.not_found'));
        }   



        if($request->value == '1'){
        $approvalRequest->approved = '1';
        $approvalRequest->approved_by = Auth::id();


        
        $customer =$approvalRequest->user->customer;
        if($customer){
            $customer->is_verified = '1';
            $customer->save();
        }


        $approvalRequest->save();
        }else{
            $approvalRequest->approved = '0';
            $approvalRequest->approved_by = null;
            $customer =$approvalRequest->user->customer;
            if($customer){
                $customer->is_verified = '0';
                $customer->save();
            }
            $approvalRequest->save();
        }

        
        return jsonResponse(true, 200, __('messages.updates_successfully' ));

    }
}