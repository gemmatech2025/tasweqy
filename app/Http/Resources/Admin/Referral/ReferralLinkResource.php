<?php

namespace App\Http\Resources\Admin\Referral;
use App\Http\Resources\Admin\Brand\BrandShowResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\TrackingEvent;


class ReferralLinkResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        $referralEarning = $this->referralEarning;
        $customer =  $this->referralEarning ? $this->referralEarning->user : null;
        $lastEvent = TrackingEvent::where('trackable_type', 'App\Models\ReferralLink')
            ->where('trackable_id', $this->id)
            ->where('event_type', 'purchase')
            ->latest()
            ->first();
        return [
            'id'                    => $this->id,
            'brand'                 => $this->brand->name,
            'status'                => $this->status,
            'created_at'            => $this->created_at ? $this->created_at->format('F j, Y g:i A') : null,

            'earning_precentage'    => $this->earning_precentage,
            'link'                  => $this->link,
            'link_code'             => $this->link_code,
            
            'for_user'              => $referralEarning ? $referralEarning->user->name : null ,
            'usered_at'             => $referralEarning ? $referralEarning->created_at->format('F j, Y g:i A') : null ,
            'total_clients'         => $referralEarning ? $referralEarning->total_clients : null ,
            // 'total_clients'         => $referralEarning ? $referralEarning->total_clients : null ,
            'total_earnings'         => $referralEarning ? $referralEarning->total_earnings : null ,
            'customers'             => $customer ? [
                                                    'name' => $customer->name , 
                                                    'email' => $customer->email ,
                                                    'phone' => $customer->phone ,
                                                    'code' => $customer->code , 

                                                    ]:null,
            'inactive_reason'       => $this->inactive_reason,


            'last_event'            => $lastEvent ? $lastEvent->created_at->format('F j, Y g:i A') :null
        ];
    }
}
