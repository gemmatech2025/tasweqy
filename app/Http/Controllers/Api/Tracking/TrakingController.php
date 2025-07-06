<?php

namespace App\Http\Controllers\Api\Tracking;

use App\Http\Controllers\Controller;
use App\Models\TrackingEvent;
use App\Models\ReferralLink;
use App\Models\DiscountCode;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Customer\Auth\LoginRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


use App\Services\EarningService;

class TrakingController extends Controller
{



    protected $earningService = null;

    public function __construct()
    {
        $this->earningService = new EarningService();
    }


    public function trackPixel(Request $request)
    {
        $order_total    = $request->query('order_total');
        $orderId        = $request->query('order_id');
        $refCode        = $request->query('ref');
        $event_type     = $request->query('event_type');
        $referral_type  = $request->query('referral_type');


        if(!$order_total || !$refCode || !$event_type || !$referral_type){
            return response()->json('', 422); 
        }

        $alreadyTracked = TrackingEvent::where('external_order_id', $orderId)
            ->where('event_type', 'purchase')
            ->exists();

        // if ($alreadyTracked) {
        //     return response()->json('', 204); 
        // }

        $referral=null;
        if($referral_type == 'referral_link'){
            $referral = ReferralLink::where('link_code' ,$refCode )->first();
        }else if($referral_type == 'discount_code'){
            $referral = DiscountCode::where('code' ,$refCode )->first();
        }else{
            return response()->json('', 422); 
        }

        if(!$referral){
            return response()->json('', 404); 
        }


        $event = $referral->trackingEvents()->create([
            'event_type'         => $event_type,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'external_order_id'  => $orderId,
            ]
        );

        $result = $this->earningService->recordAnEvent($order_total , $event);

        if($result['status'] == false){
            Log::error($result);
            return response()->json('', 500); 
        }

        

        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        return response($pixel)->header('Content-Type', 'image/gif');
    }



}