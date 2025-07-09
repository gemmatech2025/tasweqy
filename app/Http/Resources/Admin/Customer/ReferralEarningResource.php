<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


use App\Http\Resources\UserResource;


use App\Models\ReferralLink;
use App\Models\DiscountCode;
use App\Models\ReferralEarning;



class ReferralEarningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {




    if($this->referrable_type == ReferralLink::class) {


  return [
            'id'                      => $this->id,
            'link'                    => $this->referrable->link,
            'link_code'               => $this->referrable->link_code,

            'socialMediaPlatform'     => $this->socialMediaPlatform? $this->socialMediaPlatform->name : null,
            'totalUsage'              => $this->total_clients ,
            'brand'                   => $this->referrable->brand->name,
            'created_at'              => $this->created_at->format('F j, Y g:i A'),
            'totalEarnings'           => $this->total_earnings,
            'isActive'                => true,
        ];  

        } elseif($this->referrable_type == DiscountCode::class) {
        return [
            'id'                      => $this->id,
            'code'                    => $this->referrable->code,
            'socialMediaPlatform'     => $this->socialMediaPlatform? $this->socialMediaPlatform->name : null,
            'totalUsage'              => $this->total_clients ,
            'brand'                   => $this->referrable->brand->name,
            'created_at'              => $this->created_at->format('F j, Y g:i A'),
            'totalEarnings'           => $this->total_earnings,
            'isActive'                => true,
        ];    
        }

    



        





    }
}
