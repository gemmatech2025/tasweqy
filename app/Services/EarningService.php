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
use App\Models\Padge;
use Illuminate\Support\Facades\DB;

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

        DB::beginTransaction();

        try{

        
    
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



        $totalClients = ReferralEarning::where('user_id' ,$customer->user_id )->sum('total_clients');

        $padge = Padge::where('no_clients_from' , '<=' , $totalClients)
        ->where('no_clients_to' , '>=' , $totalClients)->first();

        if($padge){
            $customer->padge_id = $padge->id;
        }else{
            $lastPadge = Padge::orderByDesc('no_clients_to')->first();
            if($lastPadge){
                if($lastPadge->no_clients_to <= $totalClients){
                    $customer->padge_id = $lastPadge->id;
                }            
            }

        }

        $customer->total_balance -= $valueToBeAdded;
        $customer->save();
        $referralEarning->save();

        DB::commit();
        return ['status' => true];

        }
         catch (\Throwable $e) {
            DB::rollBack();
            return ['status'=>false , 'reason' => $e->getMessage() , 'event' , $trackingEvent];
            // [
            //     'message' => $e->getMessage(),
            //     'file'    => $e->getFile(),
            //     'line'    => $e->getLine(),
            // ]);
        
    }
}


}