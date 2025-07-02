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

use App\Services\CustomerWalletService;

class WalletController extends Controller
{



    

    public function getWalletDetails()
    {

        $user = Auth::user();


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
        
    $thisMonthTotal = $thisMonthLinkEarnings + $thisMonthDiscountEarnings;
    $lastMonthTotal = $lastMonthLinkEarnings + $lastMonthDiscountEarnings;


    $change = $lastMonthTotal > 0
    ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100
    : null; 





        








    // 'total_earnings',
    //     'referrable_type',
    //     'referrable_id',
    //     'total_clients',

        // return   WithdrawRequest::class; 
    }




 }