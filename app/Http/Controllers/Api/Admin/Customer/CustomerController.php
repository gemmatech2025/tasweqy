<?php

namespace App\Http\Controllers\Api\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BasController\BaseController;

use Illuminate\Http\Request;
use App\Services\WhatsAppOtpService;

use App\Models\User;
use App\Models\Customer;
use App\Models\WithdrawRequest;
use App\Models\Setting;
use App\Models\DiscountCode;
use App\Models\ReferralLink;
use App\Models\UserBlock;
use App\Models\ReferralEarning;
use App\Models\Brand;
use App\Models\WalletTransaction;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\General\CountryRequest;
use App\Http\Resources\Admin\Customer\AccountVerificationRequestResource;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Admin\Customer\UpdateAccountApprovalRequest;
use App\Http\Resources\Admin\Customer\CustomerResource;
use App\Http\Resources\Admin\Customer\CustomerDetailsResource;
use App\Http\Resources\Admin\Customer\ReferralEarningResource;
use App\Http\Resources\Admin\Customer\WithdrawRequestResource;
use App\Http\Resources\Admin\Customer\BrandResource;
use Carbon\Carbon;

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
        $query = Customer::query();

        if ($searchTerm) {

            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
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
        $columns = \Schema::getColumnListing('customers');

        foreach ($filters as $key => $value) {
            if (in_array($key, $columns)) {
                $query->where($key, $value);
            }


             if($key == 'status'){
                    if($value == 'not_verified'){
                        $query->where('is_verified', false)->where('is_blocked', false);
                    }else if($value == 'blocked'){
                        $query->where('is_blocked', true);
                    }else if($value == 'verified'){
                        $query->where('is_verified', true)->where('is_blocked', false);
                    }
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
        $searchTerm = $request->input('searchTerm', '');
        $filters = $request->input('filter', []);
        $query = Customer::where('is_blocked', true);

        if ($searchTerm) {
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

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
        $searchTerm = $request->input('searchTerm', '');
        $filters = $request->input('filter', []);


        $query = Customer::select('customers.*')
            ->join('users', 'users.id', '=', 'customers.user_id')
            ->leftJoin('referral_earnings', 'referral_earnings.user_id', '=', 'users.id')
            ->selectRaw('SUM(referral_earnings.total_earnings) as total_earnings')
            ->selectRaw('SUM(referral_earnings.total_clients) as total_clients')
            ->groupBy('customers.id')
            ->orderByDesc('total_earnings');




            if ($searchTerm) {
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }



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















    public function getCustomersWithBalance(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');
        $payment_status = $request->input('payment_status', ''); // 'qualified','disqualified'

        $query = Customer::query();
        $limit = Setting::where('key' , 'max_withdraw_amount')->first();
        // dd($limit->value > 200);

        if ($searchTerm) {
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($payment_status) {
            if ($payment_status == 'qualified') {
                $query->where('total_balance', '>=', $limit->value);
            } else if ($payment_status == 'disqualified') {
                $query->where('total_balance', '<', $limit->value);
            }
        }



        $customers = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $customers->map(function ($customer) use($limit) {
            $totalEarnings = ReferralEarning::where('user_id' ,$customer->user_id)->sum('total_earnings');
            $withdrawn_amount = WithdrawRequest::where('user_id' ,$customer->user_id)->where('status' ,'approved')->sum('total');
            $customer->total_balance =  $totalEarnings -  $withdrawn_amount;
            $customer->save();

            return [
                'id' => $customer->id,
                'name' => $customer->user->name,
                'email' => $customer->user->email,
                'phone' => $customer->user->phone,
                'code' => $customer->user->code,
                'withdrawn_amount' => $withdrawn_amount,
                'total_balance' => $customer->total_balance,
                'total_earnings' => $totalEarnings ?? 0,
                'can_withdraw' => $customer->total_balance > $limit->value,

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



    public function getCustomersWaletTransactions(Request $request , $customer_id)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');
        $type = $request->input('type', ''); // 'withdraw','referral_link_earnings','discount_code_earnings'

        $customer = Customer::find($customer_id);        
        if(!$customer){
            return jsonResponse(false, 404, __('messages.not_found'));
        }     
        
        

        $query = WalletTransaction::where('user_id' , $customer->user_id);

        if ($searchTerm) {
            $query->where('code', 'LIKE', "%{$searchTerm}%");
        }

        if ($type) {
            if ($type == 'withdraw') {
                $query->where('type', 'withdraw');
            }else if ($type == 'referral_link_earnings') {
                $query->where('type', 'referral_link');
            }else if ($type == 'discount_code_earnings') {
                $query->where('type', 'discount_code');
            }
        }


        $transactions = $query->paginate($perPage, ['*'], 'page', $page);
        $totalEarnings = ReferralEarning::where('user_id' ,$customer->user_id)->sum('total_earnings');
        $withdrawn_amount = WithdrawRequest::where('user_id' ,$customer->user_id)->where('status' ,'approved')->sum('total');
        $customer->total_balance = $totalEarnings -$withdrawn_amount;
        $customer->save();
        $data['customer_info'] = [
            'id' =>$customer->id ,
            'name' => $customer->user->name ,
            'totalEarnings' => $totalEarnings ,
            'withdrawn_amount' => $withdrawn_amount,
            'total_balance' => $customer->total_balance,

        ]; 


        $data['transactions_data']['transactions'] = $transactions->map(function ($transaction) {
    $transatable = $transaction->transatable;

    $withdraw_method = null;

    if ($transaction->type === 'withdraw' && $transatable) {
        $withdraw_method = $transatable->withdrawable_type === 'App\Models\PaypalAccount' ? 'paypal' : 'bank';
    }

    return [
        'id' => $transaction->id,
        'code' => $transaction->code,

        'created_at' => $transaction->created_at->format('F j, Y g:i A'), // fixed typo from create_at to created_at
        'type' => $transaction->type,
        'total' => $transaction->amount,
        'withdraw_method' => $withdraw_method,
    ];
});

        $pagination = [
            'total' => $transactions->total(),
            'current_page' => $transactions->currentPage(),
            'per_page' => $transactions->perPage(),
            'last_page' => $transactions->lastPage(),
        ];
        $data['transactions_data']['meta']=$pagination;

        return jsonResponse(true, 200, __('messages.success'), $data);
    }





    public function getNumbersForReports(Request $request)
    {

        $filter = $request->input('page', 'this_year'); // 'this_year' , 'this_month' , 'this_week' , 'today'

        $now = Carbon::now();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();


        $totalCustomers = Customer::count();
        $activecustomers = Customer::whereHas('user', function ($q) {
                    $q->whereHas('referralEarnings');
                })->count();
        $totalEarnings = ReferralEarning::sum('total_earnings');
        $totalClients  = ReferralEarning::sum('total_clients');
        $totalBrands = Brand::count();


        $lastMonthCustomers = Customer::where('created_at', '<=', $endOfLastMonth)->count();
        $lastMonthActiveCustomers = Customer::whereHas('user', function ($q) use ($endOfLastMonth) {
            $q->whereHas('referralEarnings', function ($q2) use ($endOfLastMonth) {
                $q2->where('created_at', '<=', $endOfLastMonth);
            });
        })->where('created_at', '<=', $endOfLastMonth)->count();

        $lastMonthEarnings = ReferralEarning::where('created_at', '<=', $endOfLastMonth)->sum('total_earnings');
        $lastMonthClients  = ReferralEarning::where('created_at', '<=', $endOfLastMonth)->sum('total_clients');
        $lastMonthBrands = Brand::where('created_at', '<=', $endOfLastMonth)->count();


        $totalCustomersGross  = $this->calculatePercentageChange($totalCustomers  , $lastMonthCustomers);
        $activecustomersGross = $this->calculatePercentageChange($activecustomers , $lastMonthActiveCustomers);
        $totalEarningsGross   = $this->calculatePercentageChange($totalEarnings   , $lastMonthEarnings);
        $totalClientsGross    = $this->calculatePercentageChange($totalClients    , $lastMonthClients);
        $totalBrandsGross     = $this->calculatePercentageChange($totalBrands     , $lastMonthBrands);



        $topCustomers = Customer::select('customers.*')
        ->join('users', 'users.id', '=', 'customers.user_id')
        ->leftJoin('referral_earnings', 'referral_earnings.user_id', '=', 'users.id')
        ->selectRaw('SUM(referral_earnings.total_earnings) as total_earnings, SUM(referral_earnings.total_clients) as total_clients')
        ->groupBy('customers.id')
        ->orderByDesc('total_earnings')
        ->take(5)
        ->get()->map(function ($customer) {
            return [
                'name' => $customer->user->name,
                'email' => $customer->user->email,
                'image' => $customer->user->image ? asset( $customer->user->image) : null,
                'total_clients' => $customer->total_clients ?? 0,
                'total_earnings' => $customer->total_earnings ?? 0  ,
            ];
        });


        $lastWithdrawRequests = WithdrawRequest::with('user') 
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($request) {
                return [
                    'name' => $request->user->name,
                    'email' => $request->user->email,
                    'amount' => $request->total,
                    'status' => $request->status,
                    'created_at' => $request->created_at->format('Y-m-d H:i'),
                ];
            });

        return jsonResponse(
            true,
            200,
            __('messages.success'),
            [
                'totalCustomers'  => ['number' => $totalCustomers , 'change' => $totalCustomersGross],
                'activecustomers' => ['number' => $activecustomers , 'change' => $activecustomersGross],
                'totalEarnings'   => ['number' => $totalEarnings , 'change' => $totalEarningsGross],
                'totalClients'    => ['number' => $totalClients , 'change' => $totalClientsGross],
                'totalBrands'     => ['number' => $totalBrands , 'change' => $totalBrandsGross],
                'topCustomers'    => $topCustomers,
                'lastWithdrawRequests' => $lastWithdrawRequests
            ]
        );
    }


    function calculatePercentageChange($current, $last) {
    if ($last == 0) return 100; 
        return round((($current - $last) / $last) * 100, 2); 
    }




    public function getAllForDropDown(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $searchTerm = $request->input('searchTerm', '');
        $query = User::where('role' , 'customer');

        if ($searchTerm) {

            $query->where('name', 'LIKE', "%{$searchTerm}%")
                ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
        }

        $data = $query->paginate($perPage, ['*'], 'page', $page);
        $users =  $data->map(function ($user){
            return [
                'id' => $user->id,
                'name' => $user->name,
                'completed_profile' => $user->customer ? true :false ,

            ];
        });


        return jsonResponse(true, 200, __('messages.success' ),  $users);
    }




}