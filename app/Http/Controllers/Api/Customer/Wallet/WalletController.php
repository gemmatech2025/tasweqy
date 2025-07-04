<?php

namespace App\Http\Controllers\Api\Customer\Wallet;

use App\Http\Controllers\Controller;


use Illuminate\Support\Facades\DB;



use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\WithdrawRequest;
use App\Models\ReferralEarning;
use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\WalletTransaction;


use App\Http\Resources\Customer\Wallet\WithdrawRequestResource;
use App\Http\Requests\Customer\Wallet\WithdrawRequestRequest;
use App\Http\Requests\Customer\Wallet\UpdateWithdrawRequestRequest;
use App\Http\Resources\Customer\Referral\ReferralEarningResource;
use App\Http\Resources\Customer\Wallet\WalletTransactionResource;



use App\Services\CustomerWalletService;

class WalletController extends Controller
{



    

    public function getWalletDetails()
    {

        $user = Auth::user();
        $customer = $user->customer;

        if(!$customer){
            return jsonResponse( false ,  400 ,__('messages.complete_profile_first')  );        
        }


        $totalLinkEarnings = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', ReferralLink::class)
            ->sum('total_earnings');

        $totalDiscountCodeEarnings = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->sum('total_earnings');

        $referralEarning = ReferralEarning::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        $transaction = WalletTransaction::where('user_id', $user->id)->orderBy('created_at', 'desc')
            ->take(3)
            ->get();



        $startOfThisMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfLastMonth = Carbon::now()->startOfMonth()->subSecond(); 


        $thisMonthLinkEarnings = ReferralEarning::where('user_id', $user->id)
        ->where('referrable_type', ReferralLink::class)
        ->whereBetween('created_at', [$startOfThisMonth, now()])
        ->sum('total_earnings');

        $thisMonthDiscountEarnings = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->whereBetween('created_at', [$startOfThisMonth, now()])
            ->sum('total_earnings');

        $lastMonthLinkEarnings = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', ReferralLink::class)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_earnings');

        $lastMonthDiscountEarnings = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_earnings');






        $thisMonthLinkClients = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', ReferralLink::class)
            ->whereBetween('created_at', [$startOfThisMonth, now()])
            ->sum('total_clients');

        $thisMonthDiscountClients = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->whereBetween('created_at', [$startOfThisMonth, now()])
            ->sum('total_clients');

        $lastMonthLinkClients = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', ReferralLink::class)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_clients');

        $lastMonthDiscountClients = ReferralEarning::where('user_id', $user->id)
            ->where('referrable_type', DiscountCode::class)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_clients');
            
        $thisMonthTotal = $thisMonthLinkEarnings + $thisMonthDiscountEarnings;
        $lastMonthTotal = $lastMonthLinkEarnings + $lastMonthDiscountEarnings;




        $data = [
            'total_balance' => $customer->total_balance,
            'walet_transactions' => WalletTransactionResource::collection($transaction),
            'referral_earnings' => ReferralEarningResource::collection($referralEarning),

            'monthly_comparison'=>[
                'earnings'       => ['this_mounth' => $thisMonthTotal ,'last_mounth' => $lastMonthTotal],
                'referral_links' => ['this_mounth' => $thisMonthLinkClients ,'last_mounth' => $lastMonthLinkClients],
                'discount_codes' => ['this_mounth' => $thisMonthDiscountClients ,'last_mounth' => $lastMonthDiscountClients],]
        ];

        return jsonResponse(
            true, 200, __('messages.success'),
            $data
        );  
    }







    public function getEarnings(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $orderBy = $request->input('sort_by', 'newest'); // total_client - earnings
        $socialMediaPlatform = $request->input('socialMediaPlatform', ''); // total_client - earnings
        $source = $request->input('source', ''); // referral_link - discount_code


        $user = Auth::user();
        $customer = $user->customer;

        if(!$customer){
            return jsonResponse( false ,  400 ,__('messages.complete_profile_first')  );        
        }






        $query = ReferralEarning::where('user_id', $user->id);

        if($source == 'referral_link'){
            $query->where('referrable_type' , ReferralLink::class);
        }else if($source == 'discount_code'){
            $query->where('referrable_type' , DiscountCode::class);
        }

        if($socialMediaPlatform){
            $query->where('social_media_platform_id' , $socialMediaPlatform);
        }

        if($orderBy == 'total_client'){
            $query->orderBy('total_clients', 'desc');
        }else if($orderBy == 'earnings'){
            $query->orderBy('total_earnings', 'desc');

        }else{
            $query->orderBy('created_at', 'desc');
        }


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
            ReferralEarningResource::collection($data),
            $pagination
        );
    }




    public function getTransactions(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $orderBy = $request->input('sort_by', ''); // total_client - earnings
        $actionType = $request->input('actionType', ''); // withdraw - referral_links - discount_code
        $status = $request->input('status', ''); // 'approved' , 'rejected' , 'pending'
        $earning_order = $request->input('earning_order', ''); // 'asc' , 'desc' 


        $user = Auth::user();
        $customer = $user->customer;

        if(!$customer){
            return jsonResponse( false ,  400 ,__('messages.complete_profile_first')  );        
        }






        $query = WalletTransaction::where('user_id', $user->id);

        // if($actionType == 'withdraw' || $actionType == 'discount_code' || $actionType == 'referral_link' ){
        //     $query->where('type' ,$actionType);
        // }


        // if($status == 'approved' || $status == 'rejected' || $status == 'pending'){
        //     $query->where('status' ,$status);
        // }


        if($actionType ){
            $query->where('type' ,$actionType);
        }


        if($status){
            $query->where('status' ,$status);
        }

        if($earning_order == 'desc' || $earning_order == 'asc'){
            $query->orderBy('amount', $earning_order);
        }

        if($orderBy == 'newest'){
            $query->orderBy('created_at', 'desc');
        }


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
            WalletTransactionResource::collection($data),
            $pagination
        );
    }









    




 }