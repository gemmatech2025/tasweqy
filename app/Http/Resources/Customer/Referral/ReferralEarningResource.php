<?php

namespace App\Http\Resources\Customer\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Models\ReferralLink;
use App\Models\DiscountCode;


class ReferralEarningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {

        $referalble =null;


        if($this->referrable_type == ReferralLink::class){


            if($referalble){
                $referalble =[
                'id' => $this->referrable_id ,
                'referral'  => $this->referrable->link ,
                'brand' => ['id' => $this->referrable->brand->id,
                            'name' => $this->referrable->brand->name
                        ],
                'type'  => 'referral_link'
            ];
            }
            

        }else if($this->referrable_type == DiscountCode::class){
            if($referalble){

            $referalble =[
                'id' => $this->referrable_id ,
                'referral'  => $this->referrable->code ,
                'brand' => ['id' => $this->referrable->brand->id,
                            'name' => $this->referrable->brand->name
                        ],
                'type'  => 'discount_code'
            ];
            }
        }


       
          return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'social_media_platform' => $this->socialMediaPlatform ? [
                'id' => $this->socialMediaPlatform->id,
                'name' => $this->socialMediaPlatform->name,
                'logo' => $this->socialMediaPlatform->logo ? asset($this->socialMediaPlatform->logo): null
                ] : null,
            'total_earnings' => $this->total_earnings,
            'total_clients' => $this->total_clients,

            'referrable' => $referalble,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
