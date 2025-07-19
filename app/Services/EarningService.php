<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTransaction;
use App\Models\Customer;
use App\Models\TrackingEvent;
use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;


use Illuminate\Support\Str;
use App\Services\FirebaseService;


class EarningService
{

    protected $firebaseService = null;
    
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public function recordAnEvent($amount  ,TrackingEvent $trackingEvent)
    {
    
        $trackable         = $trackingEvent->trackable;
        $precentage        = $trackable->earning_precentage;
        $referralEarning   = $trackable->referralEarning;
        if(!$referralEarning){
            return ['status' => false , 'reason' => "not assigned to user" , 'event' => $trackingEvent];
        }



        $customer          = $referralEarning->user->customer;
        if(!$customer){
            return ['status' => false , 'reason' => "customer dose not have profile" , 'event' => $trackingEvent];
        }

        $this->firebaseService->handelNotification($referralEarning->user, 'earning_added' , $referralEarning->id );

        $valueToBeAdded = $precentage * $amount;
        $referralEarning->total_clients += 1;
        $referralEarning->total_earnings += $valueToBeAdded;
        // $customer->total_balance += $valueToBeAdded;

        $code = random_int(100000, 999999);

        $type = '';
        if($trackingEvent->trackable_type == ReferralLink::class){
            $type = 'referral_link';
        }else{
            $type = 'discount_code';
        }

        $trackingEvent->walletTransaction()->create(
            [
                'code'       => $code,
                'amount'     => $valueToBeAdded,
                'status'     => 'approved',
                'type'       => $type,
                'user_id'    => $customer->user_id
            ]
        );


        $customer->total_balance -= $valueToBeAdded;
        $customer->save();
        $referralEarning->save();


        return ['status' => true];
    }


}