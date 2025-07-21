<?php

namespace App\Http\Resources\Admin\Brand;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;




use App\Models\ReferralEarning;
use App\Models\ReferralLink;
use App\Models\DiscountCode;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        
        $totalCodes = ReferralEarning::where('user_id' , $this->id)
        ->where('referrable_type' , DiscountCode::class)->count();

        $totalLinks = ReferralEarning::where('user_id' , $this->id)
        ->where('referrable_type' , ReferralLink::class)->count();

        $totalEarnings = ReferralEarning::where('user_id' , $this->id)->sum('total_earnings');


        $customer = $this->customer;



        return [
            'id'                        => $customer->id,
            'name'                      => $this->name,
            'total_codes'               => $totalCodes,
            'total_links'               => $totalLinks,
            'total_earnings'            => $totalEarnings,
        ];       
    }
}
