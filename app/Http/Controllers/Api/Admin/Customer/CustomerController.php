<?php

namespace App\Http\Controllers\Api\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\Customer\AccountVerificationRequestResource;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;

use App\Models\WithdrawRequest;



use App\Http\Requests\Admin\Customer\UpdateAccountApprovalRequest;
use App\Http\Resources\Admin\Customer\CustomerResource;
use App\Http\Resources\Admin\Customer\CustomerDetailsResource;
use App\Http\Resources\Admin\Customer\ReferralEarningResource;
use App\Http\Resources\Admin\Customer\WithdrawRequestResource;
use App\Http\Resources\Admin\Customer\BrandResource;



use App\Models\DiscountCode;
use App\Models\ReferralLink;

use App\Models\ReferralEarning;
use App\Models\Brand;


class CustomerController extends Controller
{


    protected $searchService = null;
    public function __construct()
    {
        $this->searchService = new SearchService();
    }

    

    public function getCustomers(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $query =Customer::query();

        $data = $query->paginate($perPage, ['*'], 'page', $page);


         $pagination = [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'last_page' => $data->lastPage(),
            ];


        return jsonResponse(true, 200, __('messages.success' ),  CustomerResource::collection($data) ,$pagination);
    }




    public function show($id)
    {
        $customer = Customer::find($id); 
        if(!$customer){
        return jsonResponse(false, 404, __('messages.not_found'));
        }      

        return jsonResponse(true, 200, __('messages.success' ), new CustomerDetailsResource($customer));

    }




    public function getAllReferral(Request $request , $id , $type)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $customer = Customer::find($id); 
        
        if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        }      

        $user = $customer->user;

        if($type == 'referral_link'){
            $query = $user->referralEarnings()->where('referrable_type' ,ReferralLink::class)->orderByDesc('created_at');
            $data = $query->paginate($perPage, ['*'], 'page', $page);
            $pagination = [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'last_page' => $data->lastPage(),
            ];

            return jsonResponse(true, 200, __('messages.success' ), ReferralEarningResource::collection($data) ,$pagination);
        }elseif($type == 'discount_code'){
            $query = $user->referralEarnings()->where('referrable_type' ,DiscountCode::class)->orderByDesc('created_at');
            $data = $query->paginate($perPage, ['*'], 'page', $page);
            $pagination = [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'last_page' => $data->lastPage(),
            ];

            return jsonResponse(true, 200, __('messages.success' ), ReferralEarningResource::collection($data) , $pagination);
        }else{
            return jsonResponse(false, 400, __('messages.invalid_type'));
        }
    }




    public function walletWithdrawRequests(Request $request , $id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $customer = Customer::find($id); 
        
        if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        }      

     
            $query = WithdrawRequest::where('user_id' ,$customer->user->id)->orderByDesc('created_at');
            $data = $query->paginate($perPage, ['*'], 'page', $page);
            $pagination = [
                'total' => $data->total(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'last_page' => $data->lastPage(),
            ];

            return jsonResponse(true, 200, __('messages.success' ), WithdrawRequestResource::collection($data) ,$pagination);
      
    }



    // public function getBrands(Request $request , $id)
    // {
    //     $page = $request->input('page', 1);
    //     $perPage = $request->input('per_page', 20);
    //     $customer = Customer::find($id); 
        
    //     if(!$customer){
    //         return jsonResponse(false, 404, __('messages.not_found'));
    //     }      
    
    //         $query = Brand::whereHas('referralLinks' , function ($query) {
    //             $query->whereHas('referralEarnings', function ($q) {
    //                 $q->where('user_id',$customer->user->id);
    //             });
    //         })->orWhereHas('discountCodes', function ($query) {
    //             $query->whereHas('referralEarnings', function ($q) {
    //                 $q->where('user_id',$customer->user->id);
    //             });
    //         })->orderByDesc('created_at');

    //         $data = $query->paginate($perPage, ['*'], 'page', $page);
    //         $pagination = [
    //             'total' => $data->total(),
    //             'current_page' => $data->currentPage(),
    //             'per_page' => $data->perPage(),
    //             'last_page' => $data->lastPage(),
    //         ];

    //         return jsonResponse(true, 200, __('messages.success' ), WithdrawRequestResource::collection($data) ,$pagination);
      
    // }




    public function getBrands(Request $request , $id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        $customer = Customer::find($id); 
        
        if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        }      

        $userId = $customer->user->id;

        $query = Brand::where(function ($q) use ($userId) {
            $q->whereHas('referralLinks', function ($query) use ($userId) {
                $query->whereHas('referralEarning', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })->orWhereHas('discountCodes', function ($query) use ($userId) {
                $query->whereHas('referralEarning', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            });
        })->orderByDesc('created_at');




        $data = $query->paginate($perPage, ['*'], 'page', $page);

        foreach ($data as $brand) {

            $brandId = $brand->id;
            $brand->total_clients = ReferralEarning::whereHas('referrable', function ($q) use ($brandId) {
                    $q->where('brand_id', $brandId);
                })
                ->where('user_id', $userId)
                ->sum('total_clients');


            $brand->total_earnigns = ReferralEarning::whereHas('referrable', function ($q) use ($brandId) {
                    $q->where('brand_id', $brandId);
                })
                ->where('user_id', $userId)
                ->sum('total_earnings');

            $firstJoin = ReferralEarning::whereHas('referrable', function ($q) use ($brandId) {
                    $q->where('brand_id', $brandId);
                })
                ->where('user_id', $userId)
                ->orderBy('created_at', 'asc')->first();
            

            if($firstJoin){
                $brand->first_join = $firstJoin ? $firstJoin->created_at->format('F j, Y g:i A') : null;
            } else {
                $brand->first_join = null;
            }
            
            

        }
        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        return jsonResponse(true, 200, __('messages.success'), BrandResource::collection($data), $pagination);
    }

}