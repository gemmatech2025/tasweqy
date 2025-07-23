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
use App\Models\UserBlock;


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
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');
        $filters = $request->input('filter', []);

        $query =Customer::query();
        if($searchTerm){
            $query->whereHas('user' , function($q){
                $q->where('name' , "LIKE" , "%$searchTerm%")
                ->orWhere('email' , "LIKE" , "%$searchTerm%")
                ->orWhere('phone' , "LIKE" , "%$searchTerm%");
                
            });
        }

        $filters = array_map(function ($value) {
        if (is_string($value)) {
                $lower = strtolower($value);
                return match ($lower) {
                    'true' => 1,
                    'false' => 0,
                    default => is_numeric($value) ? $value + 0 : $value,
                };
            }
            return $value;
        }, $filters);

        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

        foreach ($filters as $key => $value) {
            if (in_array($key, $columns)) {
                $query->where($key, $value);
            }
        }

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
        $perPage = $request->input('per_page', 10);
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
        $perPage = $request->input('per_page', 10);
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



    public function getBrands(Request $request , $id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

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



    public function getBlockedCustomers(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);


        $query = Customer::where('is_blocked', true);


        $data = $query->paginate($perPage, ['*'], 'page', $page);

        $pagination = [
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'last_page' => $data->lastPage(),
        ];

        $blockedCustomers = [];

        foreach ($data as $customer) {

            $blockDetails = UserBlock::where('customer_id', $customer->id)
                ->where('type', 'block')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$blockDetails) {

                $customer->is_blocked = false;
                $customer->save();

                continue;
            }

            $hasUnblockAfter = UserBlock::where('customer_id', $customer->id)
                ->where('type', 'unblock')
                ->where('created_at', '>', $blockDetails->created_at)
                ->exists();

            if ($hasUnblockAfter) {
                $customer->is_blocked = false;
                $customer->save();
                continue;
            }

            $user = $customer->user;

            $blockedCustomers[] = [
                'customer_id' => $customer->id,

                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'code' => $user->code,


                'block_id' => $blockDetails->id,


                'block_created_at' => $blockDetails->created_at->format('F j, Y g:i A'),
                'block_reason' => $blockDetails->reason,
                'creator' => $blockDetails->creator->name,


            ];



        }


        return jsonResponse(true, 200, __('messages.success'),$blockedCustomers, $pagination);
    }




    public function getBlockedCustomerDetails($id)
    {
        $blockData = UserBlock::find($id);
        if (!$blockData) {
            return jsonResponse(false, 404, __('messages.not_found'));
        }
        $user = $blockData->customer->user;
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'code' => $user->code,
            'block_id' => $blockData->id,
            'customer_id' => $blockData->customer_id,
            'type' => $blockData->type,
            'reason' => $blockData->reason,
            'creator' => $blockData->creator ? [
                'id' => $blockData->creator->id,
                'name' => $blockData->creator->name,
            ] : null,
             'images'                => $blockData->images->map(function ($image){
                    return [
                        'id' => $image->id,
                        'image' => asset($image->image),
                    ];
                 }) ,
            'created_at' => $blockData->created_at->format('F j, Y g:i A'),
        ];
        return jsonResponse(true, 200, __('messages.success'),$data);
    }

    public function getDistinguishedCustomers(Request $request)
{
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);

    $query = Customer::select('customers.*')
        ->join('users', 'users.id', '=', 'customers.user_id')
        ->leftJoin('referral_earnings', 'referral_earnings.user_id', '=', 'users.id')
        ->selectRaw('SUM(referral_earnings.total_earnings) as total_earnings')
        ->selectRaw('SUM(referral_earnings.total_clients) as total_clients')
        ->groupBy('customers.id')
        ->orderByDesc('total_earnings');

    $customers = $query->paginate($perPage, ['*'], 'page', $page);

    $data = $customers->map(function ($customer) {
        return [
            'id' => $customer->id,
            'name' => $customer->user->name,
            'email' => $customer->user->email,
            'phone' => $customer->user->phone,
            'code' => $customer->user->code,
            'total_earnings' => $customer->total_earnings ?? 0,
            'total_clients' => $customer->total_clients ?? 0,
        ];
    });

    $pagination = [
        'total' => $customers->total(),
        'current_page' => $customers->currentPage(),
        'per_page' => $customers->perPage(),
        'last_page' => $customers->lastPage(),
    ];

    return jsonResponse(true, 200, __('messages.success'), $data, $pagination);
}


   public function getNumbers()
    {
        $totalCustomers = Customer::count();
        $activecustomers = Customer::whereHas('user', function ($q) {
                    $q->whereHas('referralEarnings');
                })->count();

        $inactiveCustomers = Customer::whereHas('user', function ($q) {
            $q->whereDoesntHave('referralEarnings');
        })->count();


        $blockedCustomer = Customer::where('is_blocked' , true)->count();

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'totalCustomers' => $totalCustomers,
                'activecustomers' => $activecustomers,
                'inactiveCustomers' => $inactiveCustomers,
                'blockedCustomer' => $blockedCustomer,
            ]
        );
    }


}